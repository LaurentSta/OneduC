<?php

namespace App\Http\Controllers\Observateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Progression;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgressionController extends Controller
{
    public function index(Request $request, ?User $user = null)
    {
        $observer = auth()->user();
        $observedGroupIds = $observer->groupesObserve()->pluck('groups.id');
        $view = $request->route('view') ?? $request->query('view', 'groupes');
        $groupId = (int) $request->query('group_id', 0);
        $search = trim((string) $request->query('search', ''));

        $groupesList = Group::query()
            ->whereIn('id', $observedGroupIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($view === 'groupes') {
            $groupes = Group::query()
                ->whereIn('id', $observedGroupIds)
                ->when($search !== '', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->paginate(15, ['id', 'name'])
                ->withQueryString();

            $groupes->getCollection()->transform(function ($group) {
                $stagiaires = User::query()
                    ->join('group_user', 'users.id', '=', 'group_user.user_id')
                    ->where('group_user.group_id', $group->id)
                    ->where('users.role', 'stagiaire')
                    ->select('users.id')
                    ->get();

                $stagiaireIds = $stagiaires->pluck('id');
                $group->stagiaires_count = $stagiaireIds->count();
                $group->modules_count = (int) DB::table('group_module')->where('group_id', $group->id)->count();
                $group->total_site_time = (int) User::whereIn('id', $stagiaireIds)->sum('total_site_time');
                $group->lecons_terminees_count = (int) Progression::whereIn('user_id', $stagiaireIds)->count();
                $group->taux_reussite = 0;

                if ($stagiaireIds->isNotEmpty()) {
                    $success = (int) DB::table('progressions')
                        ->join('scorm_scores', function ($join) {
                            $join->on('progressions.user_id', '=', 'scorm_scores.user_id')
                                ->on('progressions.lecture_id', '=', 'scorm_scores.lecture_id');
                        })
                        ->whereIn('progressions.user_id', $stagiaireIds)
                        ->where('scorm_scores.last_score', '>=', 50)
                        ->count();

                    $total = max(1, (int) $group->lecons_terminees_count);
                    $group->taux_reussite = (int) round(($success / $total) * 100);
                }

                return $group;
            });

            return view('observateur.progressions.groupes', [
                'profileData' => $observer,
                'groupes' => $groupes,
                'groupesList' => $groupesList,
                'totalGroupes' => $groupes->total(),
                'search' => $search,
            ]);
        }

        if ($view === 'stagiaires') {
            $query = User::query()
                ->where('role', 'stagiaire')
                ->whereHas('groupesStagiaire', function ($query) use ($observedGroupIds) {
                    $query->whereIn('groups.id', $observedGroupIds);
                });

            if ($groupId > 0) {
                abort_unless($observedGroupIds->contains($groupId), 403);

                $query->whereHas('groupesStagiaire', function ($query) use ($groupId) {
                    $query->where('groups.id', $groupId);
                });
            }

            if ($search !== '') {
                $query->where(function ($query) use ($search) {
                    $query->where('prenom', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $stagiaires = $query
                ->with(['groupesStagiaire' => function ($query) use ($observedGroupIds) {
                    $query->whereIn('groups.id', $observedGroupIds)->orderBy('name');
                }])
                ->withCount(['progressions as lecons_terminees_count'])
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString();

            $ids = $stagiaires->getCollection()->pluck('id')->all();

            $lastActivity = Progression::query()
                ->selectRaw('user_id, MAX(completed_at) as last_completed_at')
                ->whereIn('user_id', $ids)
                ->groupBy('user_id')
                ->pluck('last_completed_at', 'user_id');

            $completedCount = Progression::query()
                ->selectRaw('user_id, COUNT(*) as total')
                ->whereIn('user_id', $ids)
                ->groupBy('user_id')
                ->pluck('total', 'user_id');

            $successCount = DB::table('progressions')
                ->join('scorm_scores', function ($join) {
                    $join->on('progressions.user_id', '=', 'scorm_scores.user_id')
                        ->on('progressions.lecture_id', '=', 'scorm_scores.lecture_id');
                })
                ->whereIn('progressions.user_id', $ids)
                ->where('scorm_scores.last_score', '>=', 50)
                ->selectRaw('progressions.user_id, COUNT(*) as success')
                ->groupBy('progressions.user_id')
                ->pluck('success', 'progressions.user_id');

            $stagiaires->getCollection()->transform(function ($stagiaire) use ($lastActivity, $completedCount, $successCount) {
                $total = (int) ($completedCount[$stagiaire->id] ?? 0);
                $ok = (int) ($successCount[$stagiaire->id] ?? 0);

                $stagiaire->last_completed_at = $lastActivity[$stagiaire->id] ?? null;
                $stagiaire->taux_reussite = $total > 0 ? (int) round(($ok / $total) * 100) : 0;

                return $stagiaire;
            });

            return view('observateur.progressions.stagiaires', [
                'profileData' => $observer,
                'stagiaires' => $stagiaires,
                'groupes' => $groupesList,
                'groupId' => $groupId,
                'search' => $search,
                'totalGroupes' => $groupesList->count(),
                'totalStagiaires' => $stagiaires->total(),
            ]);
        }

        if ($view === 'stagiaire') {
            if (! $user) {
                abort(404);
            }

            $allowed = User::query()
                ->where('id', $user->id)
                ->where('role', 'stagiaire')
                ->whereHas('groupesStagiaire', function ($query) use ($observedGroupIds) {
                    $query->whereIn('groups.id', $observedGroupIds);
                })
                ->exists();

            abort_unless($allowed, 403, 'Ce stagiaire ne fait pas partie de vos groupes observés.');

            $stagiaire = User::findOrFail($user->id);
            $userId = $stagiaire->id;

            $scormTime = (int) \App\Models\ScormScore::where('user_id', $userId)->sum('session_time');
            $quizTime = (int) DB::table('quiz_attempts')->where('user_id', $userId)->sum('total_time_seconds');
            $videoStatsObj = \App\Models\VideoSegmentTracking::where('user_id', $userId)
                ->selectRaw('SUM(total_watch_time) as watch_time, COUNT(*) as segments')
                ->first();
            $videoTime = (int) ($videoStatsObj->watch_time ?? 0);
            $engagementTotal = $scormTime + $quizTime + $videoTime;

            $totalLatencySeconds = 0;
            $totalQuestionsCount = 0;
            $scormInteractions = \App\Models\ScormInteraction::where('user_id', $userId)
                ->whereIn('result', ['correct', 'wrong'])
                ->get();

            foreach ($scormInteractions as $interaction) {
                if (! $interaction->latency) {
                    continue;
                }

                try {
                    [$h, $m, $s] = array_pad(explode(':', $interaction->latency), 3, 0);
                    $totalLatencySeconds += ((int) $h * 3600 + (int) $m * 60 + (int) $s);
                    $totalQuestionsCount++;
                } catch (\Exception) {
                }
            }

            $nativeQuestions = DB::table('quiz_attempt_questions')
                ->join('quiz_attempts', 'quiz_attempt_questions.attempt_id', '=', 'quiz_attempts.id')
                ->where('quiz_attempts.user_id', $userId)
                ->whereNotNull('quiz_attempt_questions.answered_at')
                ->select('quiz_attempt_questions.time_seconds')
                ->get();

            foreach ($nativeQuestions as $question) {
                $totalLatencySeconds += (int) $question->time_seconds;
                $totalQuestionsCount++;
            }

            $averageLatencyTime = $totalQuestionsCount > 0
                ? (int) round($totalLatencySeconds / max(1, $totalQuestionsCount))
                : 0;

            $rawAnswers = \App\Models\QuizAttemptQuestion::with(['question', 'attempt.lecture.module'])
                ->whereHas('attempt', fn ($query) => $query->where('user_id', $userId))
                ->whereNotNull('answered_at')
                ->orderBy('answered_at', 'asc')
                ->get();

            $consolidatedQuestions = $rawAnswers->groupBy('question_id')->map(function ($answers) {
                $firstTry = $answers->first();
                $lastTry = $answers->last();

                return (object) [
                    'question_text' => $firstTry->question->question_text ?? 'Question supprimée',
                    'module_title' => $firstTry->attempt->lecture->module->module_title ?? 'Module inconnu',
                    'attempts_count' => $answers->count(),
                    'first_result' => (bool) $firstTry->is_correct,
                    'final_status' => $answers->contains('is_correct', 1),
                    'last_date' => $lastTry->answered_at,
                ];
            })->sortByDesc('last_date');

            $uniqueQuestions = $consolidatedQuestions->count();
            $validatedQuestions = $consolidatedQuestions->filter(fn ($question) => $question->final_status)->count();
            $tauxReussiteGlobal = $uniqueQuestions > 0
                ? (int) round(($validatedQuestions / $uniqueQuestions) * 100)
                : 0;

            $progressions = Progression::query()
                ->where('user_id', $userId)
                ->with(['lecture.section.module'])
                ->latest('completed_at')
                ->paginate(15);

            return view('observateur.progressions.stagiaire', [
                'profileData' => $observer,
                'stagiaire' => $stagiaire,
                'progressions' => $progressions,
                'engagementTotal' => $engagementTotal,
                'videoTime' => $videoTime,
                'averageLatencyTime' => $averageLatencyTime,
                'uniqueQuestions' => $uniqueQuestions,
                'validatedQuestions' => $validatedQuestions,
                'tauxReussiteGlobal' => $tauxReussiteGlobal,
                'consolidatedQuestions' => $consolidatedQuestions,
            ]);
        }

        abort(404);
    }
}
