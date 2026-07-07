<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Models\ModuleLecture;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DupliquerLecon
{
    public function execute(ModuleLecture $lecture): ModuleLecture
    {
        return DB::transaction(function () use ($lecture) {
            $duplicate = $lecture->replicate();
            $duplicate->lecture_title = $lecture->lecture_title.' (copie)';
            $duplicate->content_blocks = $this->stripScormBlockReferences($lecture->content_blocks);
            $duplicate->save();

            $siblingIds = $lecture->section->lectures()->orderBy('position')->pluck('id')
                ->reject(fn ($id) => (int) $id === (int) $duplicate->id)
                ->values();

            $originalIndex = $siblingIds->search(fn ($id) => (int) $id === (int) $lecture->id);
            $siblingIds->splice($originalIndex + 1, 0, [$duplicate->id]);

            foreach ($siblingIds->values() as $index => $id) {
                ModuleLecture::where('id', $id)->update(['position' => $index]);
            }

            return $duplicate->fresh();
        });
    }

    /**
     * A duplicated SCORM block can't keep pointing at the original's package version:
     * its folder is keyed by content_block_key, and re-importing into either lesson's
     * block would silently overwrite the other's files. Give it a fresh key instead,
     * leaving the block empty until the trainer re-imports their own SCORM zip.
     */
    private function stripScormBlockReferences(?array $blocks): ?array
    {
        if (! $blocks) {
            return $blocks;
        }

        return collect($blocks)->map(function ($block) {
            if (($block['type'] ?? null) === 'scorm') {
                $block['content_block_key'] = (string) Str::uuid();
                $block['scorm_package_version_id'] = null;
            }

            return $block;
        })->all();
    }
}
