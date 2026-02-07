<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VideoSegmentTracking;
use App\Models\ScormScore;
use App\Models\ScormInteraction;
use App\Models\ScormEvaluationScore;
use App\Models\LessonFeedback;
use App\Models\Group;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use App\Models\QuizAttemptQuestion;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\QuizAttempt;            

class StagiaireController extends Controller
{
    /** ========= DETAIL MODULE ========= */
    public function StagiaireModuleDetail($id)
    {
        $user = auth()->user();

        // Module actif + formateur + sections + leçons
        $module = Module::with(['formateur', 'sections.lectures'])
            ->active()
            ->findOrFail($id);

        // 1. Compter les réponses SCORM par leçon
        $scormCounts = ScormInteraction::where('user_id', $user->id)
            ->select('lecture_id', DB::raw('count(*) as total'))
            ->groupBy('lecture_id')
            ->pluck('total', 'lecture_id');

        // 2. Compter les réponses QUIZ NATIFS par leçon (Via les tentatives)
        $quizCounts = DB::table('quiz_attempt_questions')
            ->join('quiz_attempts', 'quiz_attempts.id', '=', 'quiz_attempt_questions.attempt_id')
            ->where('quiz_attempts.user_id', $user->id)
            ->whereNotNull('quiz_attempt_questions.answered_at') // On ne compte que les réponses données
            ->select('quiz_attempts.lecture_id', DB::raw('count(distinct quiz_attempt_questions.question_id) as total'))
            ->groupBy('quiz_attempts.lecture_id')
            ->pluck('total', 'lecture_id');

        // Statut par leçon (completed si answered >= quiz_questions_per_attempt)
        $lessonStatuses = [];
        foreach ($module->sections->flatMap->lectures as $lecture) {
            $expected = (int)($lecture->quiz_questions_per_attempt ?? 0);
            
            // On prend le max des deux sources (ou la somme, selon votre logique, mais max est plus sûr ici)
            $nbScorm = (int)($scormCounts[$lecture->id] ?? 0);
            $nbQuiz  = (int)($quizCounts[$lecture->id] ?? 0);
            $answered = max($nbScorm, $nbQuiz);

            if ($expected === 0) {
                // Si pas de questions attendues, on considère "non commencé" par défaut
                // (ou 'completed' si vous gérez la lecture simple, mais restons sur votre logique actuelle)
                $status = 'not_started';
            } elseif ($answered >= $expected) {
                $status = 'completed';
            } elseif ($answered > 0) {
                $status = 'incomplete';
            } else {
                $status = 'not_started';
            }
            $lessonStatuses[$lecture->id] = $status;
        }

        // Progression par section
        $sectionProgress = [];
        foreach ($module->sections as $section) {
            $total = $section->lectures->count();
            $completed = collect($section->lectures)
                ->filter(fn ($lec) => ($lessonStatuses[$lec->id] ?? 'not_started') === 'completed')
                ->count();
            $sectionProgress[$section->id] = ['total' => $total, 'completed' => $completed];
        }

        // Progression globale
        $totalLectures = $module->sections->flatMap->lectures->count();
        $completedLectures = collect($lessonStatuses)->filter(fn ($s) => $s === 'completed')->count();
        $progression = $totalLectures > 0 ? (int) round(($completedLectures / $totalLectures) * 100) : 0;

        return view('stagiaire.stagiaire_module_detail', compact(
            'module',
            'lessonStatuses',
            'sectionProgress',
            'progression'
        ));
    }

    /** ========= DASHBOARD (Mise à jour : Fusion SCORM + Quiz) ========= */
    public function StagiaireDashboard()
    {
        $user = auth()->user();
        $userId = $user->id;

        // --- 1. STATS VIDÉO ---
        $videoStats = VideoSegmentTracking::where('user_id', $userId)
            ->selectRaw('SUM(total_watch_time) as watch_time, COUNT(*) as segments, SUM(watch_count) - COUNT(*) as replays')
            ->first();
        
        $videoTime = (int) ($videoStats->watch_time ?? 0);
        $totalVideoSegments = (int) ($videoStats->segments ?? 0);
        $totalVideoReplays = (int) ($videoStats->replays ?? 0);

        // --- 2. TEMPS D'APPRENTISSAGE (ENGAGEMENT) ---
        // Temps SCORM
        $scormTime = (int) ScormScore::where('user_id', $userId)->sum('session_time');
        // Temps Quiz Natifs
        $quizTime = (int) DB::table('quiz_attempts')->where('user_id', $userId)->sum('total_time_seconds');
        
        // Temps total passé à "travailler" (Vidéos + Exercices)
        $engagementTotal = $scormTime + $quizTime + $videoTime;
        
        // Temps de connexion global (Session)
        $totalSiteTime = (int) ($user->total_site_time ?? 0);

        // --- 3. QUESTIONS & RÉUSSITE (FUSION) ---
        // A. SCORM
        $scormInteractions = ScormInteraction::where('user_id', $userId)
            ->whereIn('result', ['correct', 'wrong'])
            ->get();
        $scormTotal = $scormInteractions->count();
        $scormCorrect = $scormInteractions->where('result', 'correct')->count();

        // B. Quiz Natifs
        $quizQuestions = DB::table('quiz_attempt_questions')
            ->join('quiz_attempts', 'quiz_attempt_questions.attempt_id', '=', 'quiz_attempts.id')
            ->where('quiz_attempts.user_id', $userId)
            ->whereNotNull('quiz_attempt_questions.answered_at')
            ->select('quiz_attempt_questions.is_correct', 'quiz_attempt_questions.time_seconds')
            ->get();
        $quizTotal = $quizQuestions->count();
        $quizCorrect = $quizQuestions->where('is_correct', 1)->count();

        // Totaux fusionnés
        $answeredCount = $scormTotal + $quizTotal;
        $totalCorrect = $scormCorrect + $quizCorrect;
        
        // Taux de réussite global
        $tauxBonnesReponses = $answeredCount > 0 ? (int) round(($totalCorrect / $answeredCount) * 100) : 0;

        // --- 4. LATENCE MOYENNE (RÉFLEXION) ---
        $totalLatency = 0;
        // Latence SCORM
        foreach ($scormInteractions as $interaction) {
            if ($interaction->latency) {
                try {
                    [$h, $m, $s] = array_pad(explode(':', $interaction->latency), 3, 0);
                    $totalLatency += ((int)$h * 3600 + (int)$m * 60 + (int)$s);
                } catch (\Exception $e) {}
            }
        }
        // Latence Quiz (déjà en secondes)
        $totalLatency += $quizQuestions->sum('time_seconds');
        
        $averageLatencyTime = $answeredCount > 0 ? (int) round($totalLatency / $answeredCount) : 0;

        // --- 5. GROUPES & MODULES ---
        $groupes = $user->groupesStagiaire()
            ->with([
                'modules' => function($q) {
                    $q->withPivot('position')->orderBy('group_module.position', 'asc');
                },
                'modules.sections.lectures:id,section_id,module_id,quiz_questions_per_attempt',
                'instructor'
            ])
            ->get();

        $modules   = $groupes->flatMap->modules->unique('id')->values();
        $formateur = $groupes->first()?->instructor;

        // Calcul de la progression par module
        $this->attachProgressAttributes($modules, $userId);

        // Progression Globale (Moyenne des modules en cours)
        $progressionGlobale = $modules->isNotEmpty() ? (int) round($modules->avg('progression_percent')) : 0;

        // --- 6. EXTRAS (Commentaires, Evals...) ---
        $commentairesTotal = LessonFeedback::withTrashed()->where('user_id', $userId)->count();

        // On garde la logique Eval SCORM existante pour l'instant
        $scormEvalScores = ScormEvaluationScore::with('evaluation')->where('user_id', $userId)->get();
        $totalEvaluationsDone = $scormEvalScores->count();
        $bestEvaluationScore = $scormEvalScores->max('best_score');

        return view('stagiaire.index', compact(
            'progressionGlobale',
            'modules',
            'formateur',
            'engagementTotal',    // Nouveau : Temps d'apprentissage réel
            'totalSiteTime',      // Ancien : Temps de connexion
            'answeredCount',
            'tauxBonnesReponses',
            'averageLatencyTime', // Nouveau : Latence
            'videoTime',
            'commentairesTotal',
            'totalVideoSegments',
            'totalEvaluationsDone',
            'bestEvaluationScore'
        ));
    }

    /** ========= LISTE MODULES ========= */
    public function StagiaireModules()
{
    $user = Auth::user();

    // 1. On récupère le groupe avec ses modules, en demandant explicitement la colonne de pivot 'position'
    // et en triant par celle-ci.
    $group = Group::with(['modules' => function($query) {
        $query->active()
              ->with(['sections.lectures:id,section_id,module_id,quiz_questions_per_attempt'])
              ->withPivot('position') // On récupère la position définie par le formateur
              ->orderBy('group_module.position', 'asc'); // On trie !
    }])
    ->whereHas('students', fn ($q) => $q->where('email', $user->email))
    ->first(); // On prend le premier groupe trouvé

    // 2. On extrait les modules triés
    $modules = $group ? $group->modules : collect();

    // 3. On branche la progression comme tu le faisais déjà
    $this->attachProgressAttributes($modules, $user->id);

    return view('stagiaire.stagiaire_modules', ['modules' => $modules]);
}

    public function StagiaireResultats()
    {
        $user = auth()->user();
        $userId = $user->id;

        // --- 1. CALCULS GLOBAUX (Temps & Engagement) ---
        // Temps SCORM + Quiz + Vidéo
        $scormTime = (int) ScormScore::where('user_id', $userId)->sum('session_time');
        $quizTime = (int) DB::table('quiz_attempts')->where('user_id', $userId)->sum('total_time_seconds');
        
        // Stats Vidéo
        $videoStatsObj = VideoSegmentTracking::where('user_id', $userId)
            ->selectRaw('SUM(total_watch_time) as watch_time')
            ->first();
        $videoTime = (int) ($videoStatsObj->watch_time ?? 0);
        
        $engagementTotal = $scormTime + $quizTime + $videoTime;


        // --- 2. RÉCUPÉRATION ET ANALYSE DES QUESTIONS (Logique "Droit à l'erreur") ---
        
        // On récupère TOUTES les réponses, triées par date (du plus vieux au plus récent)
        $rawAnswers = QuizAttemptQuestion::with(['question', 'attempt.lecture.module'])
            ->whereHas('attempt', fn($q) => $q->where('user_id', $userId))
            ->whereNotNull('answered_at')
            ->orderBy('answered_at', 'asc') // Important : ASC pour identifier le 1er essai
            ->get();

        // On groupe par Question ID pour l'analyse
        $consolidatedQuestions = $rawAnswers->groupBy('question_id')->map(function ($answers) {
            
            $firstTry = $answers->first(); // Le tout premier essai
            $lastTry  = $answers->last();  // Le dernier en date
            
            // RÈGLE D'OR : Est considéré validé si AU MOINS UNE réponse est correcte dans l'historique
            $isValidated = $answers->contains('is_correct', 1);

            return (object) [
                'question_id'   => $firstTry->question_id,
                'question_text' => $firstTry->question->question_text ?? 'Question introuvable',
                'module_title'  => $firstTry->attempt->lecture->module->module_title ?? 'Module inconnu',
                'attempts_count'=> $answers->count(),      // Compteur de tentatives
                'first_result'  => (bool) $firstTry->is_correct, // Mémoire de la 1ère fois
                'final_status'  => $isValidated,           // Statut "Sanctuarisé"
                'last_date'     => $lastTry->answered_at,
            ];
        })->sortByDesc('last_date'); // On affiche les questions travaillées récemment en haut


        // --- 3. STATISTIQUES DÉRIVÉES ---
        
        // Latence moyenne (Temps de réflexion)
        $totalLatency = 0;
        $totalQuestions = $rawAnswers->count();
        // Pour les quiz natifs
        $totalLatency += $rawAnswers->sum('time_seconds');
        
        $averageLatencyTime = $totalQuestions > 0 ? (int) round($totalLatency / $totalQuestions) : 0;

        // Taux de réussite "Intelligent" (Basé sur les questions validées vs total questions tentées)
        $uniqueQuestionsCount = $consolidatedQuestions->count();
        $validatedQuestionsCount = $consolidatedQuestions->where('final_status', true)->count();
        
        $tauxBonnesReponses = $uniqueQuestionsCount > 0 
            ? (int) round(($validatedQuestionsCount / $uniqueQuestionsCount) * 100) 
            : 0;

        // Réessais (Questions tentées plus d'une fois)
        $reessayeCount = $consolidatedQuestions->where('attempts_count', '>', 1)->count();


        // --- 4. RÉSULTATS DÉTAILLÉS PAR MODULE (FUSION SCORM + QUIZ NATIFS) ---
        // C'est ici que j'ai ajouté la fusion pour que le détail s'affiche
        
        // A. Récupérer le SCORM
        $scormResults = ScormScore::with('lecture.module')
            ->where('user_id', $userId)
            ->get();

        // B. Récupérer les Quiz Natifs (Terminés uniquement)
        $nativeResults = QuizAttempt::with('lecture.module')
            ->where('user_id', $userId)
            ->whereNotNull('finished_at') // Seulement ceux finis
            ->orderByDesc('finished_at')
            ->get();

        // C. Fusionner les deux collections
        $resultats = $scormResults->concat($nativeResults);

        $totalSiteTime = (int) ($user->total_site_time ?? 0);
        $videoStats = $videoStatsObj;

        return view('stagiaire.stagiaire_resultats', compact(
            'resultats',
            'reessayeCount',
            'totalSiteTime',
            'engagementTotal',
            'averageLatencyTime',
            'tauxBonnesReponses',
            'videoStats',
            'consolidatedQuestions'
        ));
    }

    /** ========= HELPER PROGRESSION =========
     * Calcule la progression par module et attache:
     * - progress (pour le carrousel)
     * - progression_percent, progression_status (pour la liste)
     * Règle: completed si answered >= quiz_questions_per_attempt.
     */
    private function attachProgressAttributes($modules, int $userId): void
    {
        // 1. Bulk chargement des réponses SCORM
        $scormAnswers = ScormInteraction::where('user_id', $userId)
            ->whereNotNull('lecture_id')
            ->select('lecture_id', DB::raw('COUNT(*) as count'))
            ->groupBy('lecture_id')
            ->pluck('count', 'lecture_id');

        // 2. Bulk chargement des réponses QUIZ NATIFS
        $quizAnswers = DB::table('quiz_attempt_questions')
            ->join('quiz_attempts', 'quiz_attempts.id', '=', 'quiz_attempt_questions.attempt_id')
            ->where('quiz_attempts.user_id', $userId)
            ->whereNotNull('quiz_attempt_questions.answered_at')
            ->select('quiz_attempts.lecture_id', DB::raw('COUNT(distinct quiz_attempt_questions.question_id) as count'))
            ->groupBy('quiz_attempts.lecture_id')
            ->pluck('count', 'lecture_id');

        foreach ($modules as $module) {
            $lectures = $module->sections->flatMap->lectures;
            $total = $lectures->count();
            $completed = 0;
            $started = false;

            foreach ($lectures as $lec) {
                $expected = (int)($lec->quiz_questions_per_attempt ?? 0);
                
                // Fusion des sources
                $cntScorm = (int)($scormAnswers[$lec->id] ?? 0);
                $cntQuiz  = (int)($quizAnswers[$lec->id] ?? 0);
                $answered = max($cntScorm, $cntQuiz);

                if ($answered > 0) {
                    $started = true;
                }
                if ($expected > 0 && $answered >= $expected) {
                    $completed++;
                }
            }

            $percent = $total > 0 ? (int) floor(($completed / $total) * 100) : 0;
            $status  = $percent === 100 ? 'completed' : ($started ? 'in_progress' : 'not_started');

            // Carrousel
            $module->setAttribute('progress', $percent);
            // Liste
            $module->setAttribute('progression_percent', $percent);
            $module->setAttribute('progression_status',  $status);
        }
    }
    
}
