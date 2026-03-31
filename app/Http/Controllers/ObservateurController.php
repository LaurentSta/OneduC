<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\ModuleLecture;
use App\Models\User;
use App\Services\LearningAnalyticsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ObservateurController extends Controller
{
    public function __construct(
        private readonly LearningAnalyticsService $learningAnalytics,
    ) {
    }

    public function dashboard(): View
    {
        $user = auth()->user();
        $observedGroups = $user->groupesObserve()
            ->withCount([
                'students as stagiaires_count' => function ($query) {
                    $query->where('users.role', 'stagiaire');
                },
                'modules as modules_count',
            ])
            ->with('instructor:id,prenom,name')
            ->orderBy('groups.name')
            ->get(['groups.id', 'groups.name', 'groups.description', 'groups.instructor_id']);

        $groupIds = $observedGroups->pluck('id');

        $distinctFormateurs = $observedGroups
            ->pluck('instructor_id')
            ->filter()
            ->unique()
            ->count();

        $learnerCount = User::query()
            ->where('role', 'stagiaire')
            ->whereHas('groupesStagiaire', function ($query) use ($groupIds) {
                $query->whereIn('groups.id', $groupIds);
            })
            ->distinct('users.id')
            ->count('users.id');

        $learnerIdsByGroup = $this->resolveGroupLearnerIds($groupIds->all());
        $lectureIdsByGroup = $this->resolveGroupLectureIds($groupIds->all());
        $allLearnerIds = $learnerIdsByGroup->flatten()->unique()->values()->all();
        $allLectureIds = $lectureIdsByGroup->flatten()->unique()->values()->all();
        $snapshots = $this->learningAnalytics->collectSnapshots($allLearnerIds, $allLectureIds);
        $overallMetrics = $this->learningAnalytics->aggregateScopeMetrics($snapshots);

        $observedGroups = $observedGroups->map(function (Group $group) use ($lectureIdsByGroup, $learnerIdsByGroup, $snapshots) {
            $scopeSnapshots = $this->filterSnapshots(
                $snapshots,
                collect($learnerIdsByGroup->get($group->id, collect()))->values()->all(),
                collect($lectureIdsByGroup->get($group->id, collect()))->values()->all(),
            );
            $scopeMetrics = $this->learningAnalytics->aggregateScopeMetrics($scopeSnapshots);
            $group->last_completed_at = $scopeMetrics['last_activity_at'] ?? null;

            return $group;
        });

        return view('observateur.index', [
            'profileData' => Auth::user(),
            'groupCount' => $observedGroups->count(),
            'formateurCount' => $distinctFormateurs,
            'learnerCount' => $learnerCount,
            'avgSuccessRate' => (int) ($overallMetrics['success_rate'] ?? 0),
            'observedGroups' => $observedGroups,
        ]);
    }

    private function resolveGroupLearnerIds(array $groupIds): \Illuminate\Support\Collection
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

    private function resolveGroupLectureIds(array $groupIds): \Illuminate\Support\Collection
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

    private function filterSnapshots(\Illuminate\Support\Collection $snapshots, array $userIds, array $lectureIds): \Illuminate\Support\Collection
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
