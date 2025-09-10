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
    /** ========= DETAIL MODULE ========= */
    public function StagiaireModuleDetail($id)
    {
        $user = auth()->user();

        // Module actif + formateur + sections + leçons
        $module = Module::with(['formateur', 'sections.lectures'])
            ->active()
            ->findOrFail($id);

        // Interactions de l'utilisateur, groupées par leçon
        $interactionsByLecture = ScormInteraction::where('user_id', $user->id)
            ->get()
            ->groupBy('lecture_id');

        // Statut par leçon (completed si answered >= question_count)
        $lessonStatuses = [];
        foreach ($module->sections->flatMap->lectures as $lecture) {
            $expected = (int)($lecture->question_count ?? 0);
            $answered = (int)($interactionsByLecture->get($lecture->id)?->count() ?? 0);

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

    /** ========= DASHBOARD ========= */
    public function StagiaireDashboard()
    {
        $user = auth()->user();

        // Stats vidéo
        $videoStats = VideoSegmentTracking::where('user_id', $user->id)
            ->selectRaw('SUM(total_watch_time) as watch_time, COUNT(*) as segments, SUM(watch_count) - COUNT(*) as replays')
            ->first();

        $totalVideoWatchTime = (int) round($videoStats->watch_time ?? 0);
        $totalVideoSegments  = (int) ($videoStats->segments ?? 0);
        $totalVideoReplays   = (int) ($videoStats->replays ?? 0);

        // Scores SCORM
        $scormScores = ScormScore::with('lecture')
            ->where('user_id', $user->id)
            ->get();

        foreach ($scormScores as $score) {
            $lectureId = $score->lecture_id;

            $reponsesCorrectes = ScormInteraction::where('lecture_id', $lectureId)
                ->where('user_id', $user->id)
                ->where('result', 'correct')
                ->count();

            $questionsTotal = ScormInteraction::where('lecture_id', $lectureId)
                ->where('user_id', $user->id)
                ->whereNotNull('interaction_weighting')
                ->count();

            $score->correct_score = $reponsesCorrectes * 10;
            $score->total_score_possible = $questionsTotal * 10;
        }

        // Progression globale
        $total      = $scormScores->count();
        $completed  = $scormScores->where('is_completed', true)->count();
        $progressionGlobale = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        // Groupes + modules + formateur, avec leçons pour la progression
        $groupes = $user->groupesStagiaire()
            ->with([
                'modules.sections.lectures:id,section_id,module_id,question_count',
                'instructor'
            ])
            ->get();

        $modules   = $groupes->flatMap->modules->unique('id')->values();
        $formateur = $groupes->first()?->instructor;

        // Brancher la progression pour le carrousel
        $this->attachProgressAttributes($modules, $user->id);

        // Indicateurs divers
        $totalSiteTime  = (int) ($user->total_site_time ?? 0);
        $answeredCount  = ScormInteraction::where('user_id', $user->id)
            ->whereIn('result', ['correct', 'wrong'])
            ->count();

        $latencies = ScormInteraction::where('user_id', $user->id)
            ->whereNotNull('latency')
            ->pluck('latency');

        $latencySeconds = $latencies->map(function ($latency) {
            try {
                [$h, $m, $s] = array_pad(explode(':', $latency), 3, 0);
                return (int)$h * 3600 + (int)$m * 60 + (int)$s;
            } catch (\Exception $e) {
                return 0;
            }
        });

        $commentairesTotal = LessonFeedback::withTrashed()
            ->where('user_id', $user->id)
            ->count();

        // Évaluations SCORM
        $scormEvalScores = ScormEvaluationScore::with('evaluation')
            ->where('user_id', $user->id)
            ->get();

        $totalEvaluationsDone   = $scormEvalScores->count();
        $averageEvaluationScore = $scormEvalScores->avg('last_score');
        $bestEvaluationScore    = $scormEvalScores->max('best_score');
        $totalSuccessEvaluations = $scormEvalScores->where('best_score', '>=', 75)->count();
        $tauxReussiteEvaluation = $totalEvaluationsDone > 0
            ? round($totalSuccessEvaluations / $totalEvaluationsDone * 100, 1)
            : 0;
        $totalEvaluationTime      = (int) $scormEvalScores->sum('session_time');
        $totalEvaluationQuestions = (int) $scormEvalScores->sum('questions_answered');

        // Taux global de bonnes réponses
        $totalCorrectScore   = $scormScores->sum('correct_score');
        $totalScorePossible  = $scormScores->sum('total_score_possible');
        $tauxBonnesReponses  = $totalScorePossible > 0
            ? (int) round(($totalCorrectScore / $totalScorePossible) * 100)
            : 0;

        return view('stagiaire.index', compact(
            'scormScores',
            'progressionGlobale',
            'modules',
            'formateur',
            'totalSiteTime',
            'answeredCount',
            'tauxBonnesReponses',
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

    /** ========= LISTE MODULES ========= */
    public function StagiaireModules()
    {
        $user = Auth::user();

        // Groupes + modules du stagiaire
        $groupes = Group::with('modules')
            ->whereHas('students', fn ($q) => $q->where('email', $user->email))
            ->get();

        $moduleIds = $groupes->flatMap->modules->unique('id')->pluck('id');

        // Modules actifs + sections + leçons
        $modules = Module::whereIn('id', $moduleIds)
            ->active()
            ->with(['sections.lectures:id,section_id,module_id,question_count'])
            ->get();

        // Brancher progression pour la liste
        $this->attachProgressAttributes($modules, $user->id);

        return view('stagiaire.stagiaire_modules', ['modules' => $modules]);
    }

    /** ========= RESULTATS ========= */
    public function StagiaireResultats()
    {
        $user = auth()->user();
        $userId = $user->id;

        // Temps SCORM (session_time)
        $totalScormTime = (int) ScormScore::where('user_id', $userId)->sum('session_time');

        // Latency (HH:MM:SS -> s)
        $latencies = ScormInteraction::where('user_id', $userId)
            ->whereNotNull('latency')
            ->pluck('latency');

        $latencySeconds = $latencies->map(function ($latency) {
            try {
                [$h, $m, $s] = array_pad(explode(':', $latency), 3, 0);
                return (int)$h * 3600 + (int)$m * 60 + (int)$s;
            } catch (\Exception $e) {
                return 0;
            }
        });

        $totalLatencyTime = (int) $latencySeconds->sum();

        // Temps moyen par question
        $answeredCount = ScormInteraction::where('user_id', $userId)
            ->whereIn('result', ['correct', 'wrong'])
            ->count();

        $averageLatencyTime = $answeredCount > 0
            ? (int) round($totalLatencyTime / $answeredCount)
            : 0;

        // Temps total engagement
        $engagementTotal = $totalScormTime + $totalLatencyTime;

        // Nombre de questions réessayées
        $reessayeCount = ScormInteraction::where('user_id', $userId)
            ->get()
            ->groupBy('interaction_id')
            ->filter(fn ($g) => $g->count() > 1)
            ->count();

        // Temps total site
        $totalSiteTime = (int) ($user->total_site_time ?? 0);

        // Résultats par leçon
        $resultats = ScormScore::with('lecture')
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

            $score->total_questions       = $questionsTotal;
            $score->correct_score         = $reponsesCorrectes * 10;
            $score->total_score_possible  = $questionsTotal * 10;

            if ($score->lesson_status !== 'completed') {
                $score->lesson_status = ScormScore::where('user_id', $userId)
                    ->where('lecture_id', $lectureId)
                    ->value('lesson_status') ?? null;
            }

            $score->formatted_session_time = gmdate('H\h i\m s\s', (int)($score->session_time ?? 0));
        }

        // Stats vidéo centralisées
        $videoStats = VideoSegmentTracking::getStatsForUser($userId);

        return view('stagiaire.stagiaire_resultats', compact(
            'resultats',
            'reessayeCount',
            'totalSiteTime',
            'totalScormTime',
            'totalLatencyTime',
            'engagementTotal',
            'averageLatencyTime',
            'videoStats'
        ));
    }

    /** ========= HELPER PROGRESSION =========
     * Calcule la progression par module et attache:
     * - progress (pour le carrousel)
     * - progression_percent, progression_status (pour la liste)
     * Règle: completed si answered >= question_count.
     */
    private function attachProgressAttributes($modules, int $userId): void
    {
        // Nb de réponses par leçon pour l'utilisateur
        $answersPerLecture = ScormInteraction::where('user_id', $userId)
            ->whereNotNull('lecture_id')
            ->select('lecture_id')
            ->selectRaw('COUNT(*) as answers_count')
            ->groupBy('lecture_id')
            ->pluck('answers_count', 'lecture_id'); // lecture_id => nb réponses

        foreach ($modules as $module) {
            $lectures = $module->sections->flatMap->lectures;
            $total = $lectures->count();
            $completed = 0;
            $started = false;

            foreach ($lectures as $lec) {
                $expected = (int)($lec->question_count ?? 0);
                $answered = (int)($answersPerLecture[$lec->id] ?? 0);
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
