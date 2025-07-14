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

class StagiaireController extends Controller
{

    public function StagiaireModuleDetail($id)
        {
            $user = auth()->user();

            // 🔁 Charger le module avec les relations nécessaires
            $module = \App\Models\Module::with('formateur', 'sections.lectures')->findOrFail($id);

            // ✅ Statuts des leçons (à partir des interactions)
            $interactions = \App\Models\ScormInteraction::where('user_id', $user->id)->get()->groupBy('lecture_id');

            $lessonStatuses = [];
            foreach ($module->sections->flatMap->lectures as $lecture) {
                $expected = $lecture->question_count ?? 0;
                $answered = $interactions->get($lecture->id)?->count() ?? 0;

                if ($expected === 0) {
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

            // ✅ Progression par section
            $sectionProgress = [];
            foreach ($module->sections as $section) {
                $total = $section->lectures->count();
                $completed = collect($section->lectures)->filter(fn($lec) => $lessonStatuses[$lec->id] === 'completed')->count();
                $sectionProgress[$section->id] = ['total' => $total, 'completed' => $completed];
            }

            // ✅ Progression globale (en %)
            $totalLectures = $module->sections->flatMap->lectures->count();
            $completedLectures = collect($lessonStatuses)->filter(fn($s) => $s === 'completed')->count();
            $progression = $totalLectures > 0 ? round(($completedLectures / $totalLectures) * 100) : 0;

            return view('stagiaire.stagiaire_module_detail', compact(
                'module',
                'lessonStatuses',
                'sectionProgress',
                'progression'
            ));
        }
        // Tableau de bord stagiaire
    public function StagiaireDashboard()
        {
            $user = auth()->user();

            $videoStats = VideoSegmentTracking::where('user_id', $user->id)
            ->selectRaw('SUM(total_watch_time) as watch_time, COUNT(*) as segments, SUM(watch_count) - COUNT(*) as replays')
            ->first();

            $totalVideoWatchTime = (int) round($videoStats->watch_time ?? 0);
            $totalVideoSegments = (int) ($videoStats->segments ?? 0);
            $totalVideoReplays = (int) ($videoStats->replays ?? 0);
            // ✅ Tous les scores SCORM du stagiaire
            $scormScores = \App\Models\ScormScore::with('lecture')
                ->where('user_id', $user->id)
                ->get();

            // 🔁 Recalcul des scores pour chaque leçon
            foreach ($scormScores as $score) {
                $lectureId = $score->lecture_id;

                $reponsesCorrectes = \App\Models\ScormInteraction::where('lecture_id', $lectureId)
                    ->where('user_id', $user->id)
                    ->where('result', 'correct')
                    ->count();

                $questionsTotal = \App\Models\ScormInteraction::where('lecture_id', $lectureId)
                    ->where('user_id', $user->id)
                    ->whereNotNull('interaction_weighting')
                    ->count();

                $score->correct_score = $reponsesCorrectes * 10;
                $score->total_score_possible = $questionsTotal * 10;
            }

            // ✅ Calcul de progression globale
            $total = $scormScores->count();
            $completed = $scormScores->where('is_completed', true)->count();
            $progressionGlobale = $total > 0 ? round(($completed / $total) * 100) : 0;

            // ✅ Score global
            $totalCorrectScore = $scormScores->sum('correct_score');
            $totalScorePossible = $scormScores->sum('total_score_possible');
            $tauxBonnesReponses = $totalScorePossible > 0
                ? round(($totalCorrectScore / $totalScorePossible) * 100)
                : 0;

            // ✅ Récupération des groupes + modules + formateur
            $groupes = $user->groupesStagiaire()->with('modules', 'instructor')->get();
            $modules = $groupes->flatMap->modules->unique('id')->values();
            $formateur = $groupes->first()?->instructor;

            // ✅ Temps passé sur la plateforme
            $totalSiteTime = $user->total_site_time;

            // ✅ Temps passé sur les activités SCORM
            $totalScormTime = \App\Models\ScormScore::where('user_id', $user->id)
                ->sum('session_time');
            // ✅ Nombre de réponses (correctes ou non)
            $answeredCount = \App\Models\ScormInteraction::where('user_id', $user->id)
                ->whereIn('result', ['correct', 'wrong'])
                ->count();
            // 🔁 On récupère tous les latencies en format "HH:MM:SS"
            $latencies = \App\Models\ScormInteraction::where('user_id', $user->id)
                ->whereNotNull('latency')
                ->pluck('latency');
            // ⏱️ Convertir en secondes
            $latencySeconds = $latencies->map(function ($latency) {
                try {
                    [$h, $m, $s] = array_pad(explode(':', $latency), 3, 0);
                    return (int)$h * 3600 + (int)$m * 60 + (int)$s;
                } catch (\Exception $e) {
                    return 0; // en cas de données corrompues
                }
            });
            // ✅ Temps total à répondre aux questions
            $totalLatencyTime = $latencySeconds->sum();
            // ✅ Temps moyen de réponse (en secondes)
            $averageLatencyTime = $latencySeconds->count() > 0
                ? round($totalLatencyTime / $latencySeconds->count())
                : 0;

            $commentairesTotal = LessonFeedback::withTrashed()
            ->where('user_id', $user->id)
            ->count();

            // --- Statistiques ÉVALUATIONS SCORM ---

            $scormEvalScores = ScormEvaluationScore::with('evaluation')
                ->where('user_id', $user->id)
                ->get();

            // Globales
            $totalEvaluationsDone = $scormEvalScores->count();
            $averageEvaluationScore = $scormEvalScores->avg('last_score');
            $bestEvaluationScore = $scormEvalScores->max('best_score');
            $totalSuccessEvaluations = $scormEvalScores->where('best_score', '>=', 75)->count();
            $tauxReussiteEvaluation = $totalEvaluationsDone > 0
                ? round($totalSuccessEvaluations / $totalEvaluationsDone * 100, 1)
                : 0;
            $totalEvaluationTime = $scormEvalScores->sum('session_time');
            $totalEvaluationQuestions = $scormEvalScores->sum('questions_answered');


                return view('stagiaire.index', compact(
                    'scormScores',
                    'progressionGlobale',
                    'modules',
                    'formateur',
                    'totalSiteTime',
                    'totalScormTime',
                    'answeredCount',
                    'tauxBonnesReponses',
                    'totalLatencyTime',
                    'averageLatencyTime',
                    'commentairesTotal',
                    'totalVideoWatchTime',
                    'totalVideoSegments',
                    'totalVideoReplays',
                    'scormEvalScores',
                    'totalEvaluationsDone',
                    'averageEvaluationScore',
                    'bestEvaluationScore',
                    'tauxReussiteEvaluation',
                    'totalEvaluationTime',
                    'totalEvaluationQuestions'
                ));


        }
    public function StagiaireModules()
        {
            $user = Auth::user();

            // Récupérer tous les groupes où il est stagiaire
            $groupes = \App\Models\Group::with('modules')
                ->whereHas('students', function ($query) use ($user) {
                    $query->where('email', $user->email);
                })
                ->get();

            // Fusionner tous les modules (en supprimant les doublons)
            $modules = $groupes->flatMap->modules->unique('id');

            return view('stagiaire.stagiaire_modules', compact('modules'));
        }


    public function StagiaireResultats()
        {
            $userId = auth()->id();

            $resultats = \App\Models\ScormScore::with('lecture')
                ->where('user_id', $userId)
                ->get();

                foreach ($resultats as $score) {
                    $lectureId = $score->lecture_id;

                    $reponsesCorrectes = ScormInteraction::where('lecture_id', $lectureId)
                        ->where('user_id', $userId)
                        ->where('result', 'correct')
                        ->count();

                    $questionsTotal = ScormInteraction::where('lecture_id', $lectureId)
                        ->where('user_id', $userId)
                        ->whereNotNull('interaction_weighting')
                        ->count();

                    $score->answered_questions = ScormInteraction::where('lecture_id', $lectureId)
                        ->where('user_id', $userId)
                        ->whereIn('result', ['correct', 'wrong'])
                        ->count();

                    $score->total_questions = $questionsTotal;
                    $score->correct_score = $reponsesCorrectes * 10;
                    $score->total_score_possible = $questionsTotal * 10;

                    // 🔐 Ne réécrit pas un statut déjà 'completed'
                    if ($score->lesson_status !== 'completed') {
                        $score->lesson_status = \App\Models\ScormScore::where('user_id', $userId)
                            ->where('lecture_id', $lectureId)
                            ->value('lesson_status') ?? null;
                    }
                    // 🕒 Ajoute une version formatée du temps
                    $score->formatted_session_time = gmdate('H\h i\m s\s', $score->session_time ?? 0);

                    if ($score->lesson_status !== 'completed') {
                        $score->lesson_status = \App\Models\ScormScore::where('user_id', $userId)
                            ->where('lecture_id', $lectureId)
                            ->value('lesson_status') ?? null;
                    }

                }

            return view('stagiaire.stagiaire_resultats', compact('resultats'));
        }
}
