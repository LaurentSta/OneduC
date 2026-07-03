<?php

namespace App\Domains\ModulesFormateur\Support;

use App\Models\ModuleLecture;
use App\Models\ModuleSection;

class DonneesModule
{
    public function section(ModuleSection $section): array
    {
        return [
            'id' => $section->id,
            'section_title' => $section->section_title,
            'position' => $section->position,
        ];
    }

    public function lecture(ModuleLecture $lecture): array
    {
        return [
            'id' => $lecture->id,
            'section_id' => $lecture->section_id,
            'module_id' => $lecture->module_id,
            'lecture_title' => $lecture->lecture_title,
            'content_type' => $lecture->content_type,
            'content_blocks' => $lecture->content_blocks ?? [],
            'position' => $lecture->position,
        ];
    }
}
