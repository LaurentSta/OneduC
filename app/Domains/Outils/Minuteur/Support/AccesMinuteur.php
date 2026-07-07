<?php

namespace App\Domains\Outils\Minuteur\Support;

use App\Models\Group;

class AccesMinuteur
{
    public function assertFormateurAccess(Group $group, ?int $trainerId = null): Group
    {
        $trainerId ??= (int) auth()->id();

        return Group::query()
            ->accessibleByTrainer($trainerId)
            ->whereKey($group->id)
            ->firstOrFail();
    }

    public function assertStagiaireAccess(Group $group, ?int $userId = null): void
    {
        $userId ??= (int) auth()->id();

        abort_unless((bool) $group->is_active, 404);

        $isMember = $group->students()
            ->where('users.id', $userId)
            ->exists();

        abort_unless($isMember, 404);
    }
}
