<?php

namespace App\Domains\ModulesFormateur\Support;

use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\ScormPackageVersion;

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
            'content_blocks' => $this->resolvedContentBlocks($lecture),
            'position' => $lecture->position,
        ];
    }

    /**
     * Content blocks with each image block's URL resolved fresh from its media_id,
     * since the URL is never persisted (only the media_id is).
     */
    public function resolvedContentBlocks(ModuleLecture $lecture): array
    {
        $mediaById = $lecture->module->getMedia('lesson-images')->keyBy('id');

        return collect($lecture->content_blocks ?? [])
            ->map(function (array $block) use ($mediaById) {
                if (($block['type'] ?? null) === 'image') {
                    $media = $mediaById->get($block['media_id'] ?? null);
                    $block['url'] = $media?->getUrl('display');
                }

                if (($block['type'] ?? null) === 'scorm') {
                    $version = ScormPackageVersion::find($block['scorm_package_version_id'] ?? null);
                    $block['preview_url'] = $version?->asset_url;
                }

                return $block;
            })
            ->all();
    }
}
