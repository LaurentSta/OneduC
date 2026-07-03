<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use Illuminate\Support\Facades\DB;

class PromouvoirLeconEnChapitre
{
    public function execute(ModuleLecture $lecture): ModuleSection
    {
        abort_if(! empty($lecture->content_blocks), 422, 'Cette leçon contient déjà du contenu, elle ne peut pas être transformée en chapitre.');

        $module = $lecture->module;

        return DB::transaction(function () use ($lecture, $module) {
            $position = (int) $module->sections()->max('position') + 1;

            $section = $module->sections()->create([
                'section_title' => $lecture->lecture_title,
                'position' => $position,
            ]);

            $lecture->delete();

            return $section;
        });
    }
}
