<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Domains\CatalogueFormations\Actions\CreerVersionFormationCatalogue;
use App\Models\Module;
use Illuminate\Support\Str;

class DupliquerModuleCatalogue
{
    public function __construct(
        private readonly CreerVersionFormationCatalogue $copieurProfond,
    ) {}

    public function execute(Module $catalogModule, int $trainerId): Module
    {
        $newModule = $this->copieurProfond->dupliquerPourFormateur($catalogModule, $trainerId);
        $newTitle = trim((string) ($catalogModule->module_title ?? $catalogModule->module_name)).' (copie)';

        $newModule->forceFill([
            'module_title' => $newTitle,
            'module_name' => $newTitle,
            'module_name_slug' => Str::slug($newTitle).'-'.Str::lower(Str::random(6)),
        ])->save();

        $groupIds = $catalogModule->groups()->accessibleByTrainer($trainerId)->pluck('groups.id');
        $newModule->groups()->sync($groupIds);

        return $newModule->fresh(['sections.lectures']);
    }
}
