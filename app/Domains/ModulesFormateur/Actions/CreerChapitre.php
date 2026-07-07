<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Models\Module;
use App\Models\ModuleSection;

class CreerChapitre
{
    public function execute(Module $module, string $title): ModuleSection
    {
        $position = (int) $module->sections()->max('position') + 1;

        return $module->sections()->create([
            'section_title' => $title,
            'position' => $position,
        ]);
    }
}
