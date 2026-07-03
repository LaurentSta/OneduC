<?php

namespace App\Domains\ModulesFormateur\Support;

use App\Models\Module;

class AccesModule
{
    public function assertOwner(Module $module, ?int $trainerId = null): void
    {
        $trainerId ??= (int) auth()->id();

        abort_unless(
            (int) $module->formateur_id === $trainerId && $module->is_trainer_authored,
            403
        );
    }
}
