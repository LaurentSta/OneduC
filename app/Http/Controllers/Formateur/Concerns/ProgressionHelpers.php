<?php

namespace App\Http\Controllers\Formateur\Concerns;

use App\Models\Group;
use App\Models\ModuleLecture;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait ProgressionHelpers
{
    private function accessibleTrainerGroupIds(int $formateurId): Collection
    {
        return Group::query()
            ->accessibleByTrainer($formateurId)
            ->pluck('groups.id')
            ->map(fn ($groupId) => (int) $groupId)
            ->values();
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

        $lectureIdsByModule = $this->resolveModuleLectureIds(
            $moduleIdsByGroup->flatten()->unique()->values()->all()
        );

        return collect($groupIds)->mapWithKeys(function (int $groupId) use ($lectureIdsByModule, $moduleIdsByGroup) {
            $lectureIds = collect($moduleIdsByGroup->get($groupId, collect()))
                ->flatMap(fn ($moduleId) => $lectureIdsByModule->get((int) $moduleId, collect()))
                ->unique()
                ->values();

            return [$groupId => $lectureIds];
        });
    }

    private function resolveModuleLectureIds(array $moduleIds): Collection
    {
        $moduleIds = collect($moduleIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();

        if ($moduleIds === []) {
            return collect();
        }

        $lectureIdsByModule = ModuleLecture::query()
            ->whereIn('module_id', $moduleIds)
            ->get(['id', 'module_id'])
            ->groupBy('module_id')
            ->map(fn ($rows) => $rows->pluck('id')->map(fn ($id) => (int) $id)->unique()->values());

        return collect($moduleIds)
            ->mapWithKeys(fn (int $moduleId) => [$moduleId => $lectureIdsByModule->get($moduleId, collect())]);
    }

    private function filterSnapshots(Collection $snapshots, array $userIds, array $lectureIds): Collection
    {
        $userLookup = array_fill_keys(
            collect($userIds)
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id) => $id > 0)
                ->values()
                ->all(),
            true
        );

        $lectureLookup = array_fill_keys(
            collect($lectureIds)
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id) => $id > 0)
                ->values()
                ->all(),
            true
        );

        if ($userLookup === [] || $lectureLookup === []) {
            return collect();
        }

        return $snapshots->filter(function (array $snapshot) use ($lectureLookup, $userLookup) {
            return isset($userLookup[(int) ($snapshot['user_id'] ?? 0)])
                && isset($lectureLookup[(int) ($snapshot['lecture_id'] ?? 0)]);
        })->values();
    }
}
