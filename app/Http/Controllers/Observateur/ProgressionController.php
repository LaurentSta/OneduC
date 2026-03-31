<?php

namespace App\Http\Controllers\Observateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\ModuleLecture;
use App\Models\Progression;
use App\Models\User;
use App\Services\LearningAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProgressionController extends Controller
{
    public function __construct(
        private readonly LearningAnalyticsService $learningAnalytics,
    ) {
    }

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

            $pageGroupIds = $groupes->getCollection()->pluck('id')->all();
            $learnerIdsByGroup = $this->resolveGroupLearnerIds($pageGroupIds);
            $lectureIdsByGroup = $this->resolveGroupLectureIds($pageGroupIds);
            $snapshots = $this->learningAnalytics->collectSnapshots(
                $learnerIdsByGroup->flatten()->unique()->values()->all(),
                $lectureIdsByGroup->flatten()->unique()->values()->all(),
            );

            $groupes->getCollection()->transform(function ($group) use ($lectureIdsByGroup, $learnerIdsByGroup, $snapshots) {
                $stagiaireIds = collect($learnerIdsByGroup->get($group->id, collect()))->values();
                $groupLectureIds = collect($lectureIdsByGroup->get($group->id, collect()))->values()->all();
                $scopeSnapshots = $this->filterSnapshots($snapshots, $stagiaireIds->all(), $groupLectureIds);
                $scopeMetrics = $this->learningAnalytics->aggregateScopeMetrics($scopeSnapshots);

                $group->stagiaires_count = $stagiaireIds->count();
                $group->modules_count = (int) DB::table('group_module')->where('group_id', $group->id)->count();
                $group->total_site_time = (int) User::whereIn('id', $stagiaireIds)->sum('total_site_time');
                $group->lecons_terminees_count = (int) ($scopeMetrics['completed_count'] ?? 0);
                $group->taux_reussite = (int) ($scopeMetrics['success_rate'] ?? 0);

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
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString();

            $ids = $stagiaires->getCollection()->pluck('id')->all();
            $groupLectureIdsByGroup = $this->resolveGroupLectureIds($groupesList->pluck('id')->all());
            $allLectureIds = $groupLectureIdsByGroup->flatten()->unique()->values()->all();
            $lectureScopeIds = $groupId > 0
                ? collect($groupLectureIdsByGroup->get($groupId, collect()))->values()->all()
                : $allLectureIds;
            $scopeSnapshots = $this->filterSnapshots(
                $this->learningAnalytics->collectSnapshots($ids, $lectureScopeIds),
                $ids,
                $lectureScopeIds,
            );
            $userMetrics = $this->learningAnalytics->aggregateUserMetrics($scopeSnapshots);

            $stagiaires->getCollection()->transform(function ($stagiaire) use ($userMetrics) {
                $metrics = $userMetrics->get($stagiaire->id, [
                    'completed_count' => 0,
                    'success_rate' => 0,
                    'last_activity_at' => null,
                ]);

                $stagiaire->lecons_terminees_count = (int) ($metrics['completed_count'] ?? 0);
                $stagiaire->last_completed_at = $metrics['last_activity_at'] ?? null;
                $stagiaire->taux_reussite = (int) ($metrics['success_rate'] ?? 0);

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

    private function resolveGroupLearnerIds(array $groupIds): Collection
    {
        $groupIds = collect($groupIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();

        if ($groupIds === []) {
            return collect();
        }

        $learnerIdsByGroup = DB::table('group_user')
            ->join('users', 'users.id', '=', 'group_user.user_id')
            ->whereIn('group_user.group_id', $groupIds)
            ->where('group_user.role_in_group', 'stagiaire')
            ->where('users.role', 'stagiaire')
            ->select('group_user.group_id', 'group_user.user_id')
            ->get()
            ->groupBy('group_id')
            ->map(fn ($rows) => $rows->pluck('user_id')->map(fn ($id) => (int) $id)->unique()->values());

        return collect($groupIds)
            ->mapWithKeys(fn (int $groupId) => [$groupId => $learnerIdsByGroup->get($groupId, collect())]);
    }

    private function resolveGroupLectureIds(array $groupIds): Collection
    {
        $groupIds = collect($groupIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();

        if ($groupIds === []) {
            return collect();
        }

        $moduleIdsByGroup = DB::table('group_module')
            ->whereIn('group_id', $groupIds)
            ->select('group_id', 'module_id')
            ->get()
            ->groupBy('group_id')
            ->map(fn ($rows) => $rows->pluck('module_id')->map(fn ($id) => (int) $id)->unique()->values());

        $lectureIdsByModule = ModuleLecture::query()
            ->whereIn('module_id', $moduleIdsByGroup->flatten()->unique()->values()->all())
            ->get(['id', 'module_id'])
            ->groupBy('module_id')
            ->map(fn ($rows) => $rows->pluck('id')->map(fn ($id) => (int) $id)->unique()->values());

        return collect($groupIds)->mapWithKeys(function (int $groupId) use ($lectureIdsByModule, $moduleIdsByGroup) {
            $lectureIds = collect($moduleIdsByGroup->get($groupId, collect()))
                ->flatMap(fn ($moduleId) => $lectureIdsByModule->get((int) $moduleId, collect()))
                ->unique()
                ->values();

            return [$groupId => $lectureIds];
        });
    }

    private function filterSnapshots(Collection $snapshots, array $userIds, array $lectureIds): Collection
    {
        $userLookup = array_fill_keys($userIds, true);
        $lectureLookup = array_fill_keys($lectureIds, true);

        if ($userLookup === [] || $lectureLookup === []) {
            return collect();
        }

        return $snapshots->filter(function (array $snapshot) use ($lectureLookup, $userLookup) {
            return isset($userLookup[(int) ($snapshot['user_id'] ?? 0)])
                && isset($lectureLookup[(int) ($snapshot['lecture_id'] ?? 0)]);
        })->values();
    }
}
