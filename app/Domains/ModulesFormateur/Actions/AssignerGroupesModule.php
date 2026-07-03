<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Models\Group;
use App\Models\Module;

class AssignerGroupesModule
{
    public function execute(Module $module, array $groupIds, int $trainerId): void
    {
        $accessibleGroupIds = Group::query()
            ->accessibleByTrainer($trainerId)
            ->pluck('groups.id')
            ->map(fn ($id) => (int) $id);

        $requestedIds = collect($groupIds)->map(fn ($id) => (int) $id);

        $module->groups()->sync($requestedIds->intersect($accessibleGroupIds)->values());
    }
}
