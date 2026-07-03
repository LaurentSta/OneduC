<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Domains\ModulesFormateur\Support\NettoyeurBlocsModule;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;

class CreerLecon
{
    public function __construct(
        private readonly NettoyeurBlocsModule $blocks,
    ) {}

    public function execute(ModuleSection $section, string $title, ?string $rawBlocksJson): ModuleLecture
    {
        $position = (int) $section->lectures()->max('position') + 1;

        return $section->lectures()->create([
            'module_id' => $section->module_id,
            'lecture_title' => $title,
            'content_type' => 'blocks',
            'content_blocks' => $this->blocks->sanitizeBlocks($rawBlocksJson, (int) $section->module_id),
            'position' => $position,
        ]);
    }
}
