<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Domains\ModulesFormateur\Support\NettoyeurBlocsModule;
use App\Models\ModuleLecture;

class ModifierLecon
{
    public function __construct(
        private readonly NettoyeurBlocsModule $blocks,
    ) {}

    public function execute(ModuleLecture $lecture, string $title, ?string $rawBlocksJson, bool $updateBlocks): ModuleLecture
    {
        $updates = ['lecture_title' => $title];

        if ($updateBlocks) {
            $updates['content_blocks'] = $this->blocks->sanitizeBlocks($rawBlocksJson, (int) $lecture->module_id);
        }

        $lecture->update($updates);

        return $lecture->fresh();
    }
}
