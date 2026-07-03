<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DupliquerModuleCatalogue
{
    public function execute(Module $catalogModule, int $trainerId): Module
    {
        return DB::transaction(function () use ($catalogModule, $trainerId) {
            $newTitle = trim((string) ($catalogModule->module_title ?? $catalogModule->module_name)).' (copie)';

            $newModule = Module::create([
                'category_id' => $catalogModule->category_id,
                'subcategory_id' => $catalogModule->subcategory_id,
                'formateur_id' => $trainerId,
                'module_title' => $newTitle,
                'module_name' => $newTitle,
                'module_name_slug' => Str::slug($newTitle).'-'.Str::random(6),
                'description' => $catalogModule->description,
                'objectifs' => $catalogModule->objectifs,
                'module_video' => $catalogModule->module_video,
                'label' => $catalogModule->label,
                'duree' => $catalogModule->duree,
                'resources' => $catalogModule->resources,
                'certificat' => $catalogModule->certificat,
                'prerequi' => $catalogModule->prerequi,
                'status' => 1,
                'is_trainer_authored' => true,
            ]);

            foreach ($catalogModule->sections()->orderBy('position')->get() as $index => $section) {
                $newSection = $newModule->sections()->create([
                    'section_title' => $section->section_title,
                    'section_html' => $section->section_html,
                    'objectif' => $section->objectif,
                    'methode' => $section->methode,
                    'contexte' => $section->contexte,
                    'scorm_video_path' => $section->scorm_video_path,
                    'video_url' => $section->video_url,
                    'position' => $index,
                ]);

                foreach ($section->lectures()->orderBy('position')->get() as $lecture) {
                    $newSection->lectures()->create([
                        'module_id' => $newModule->id,
                        'lecture_title' => $lecture->lecture_title,
                        'url' => $lecture->url,
                        'position' => $lecture->position,
                        'content_type' => $lecture->content_type,
                        'content_blocks' => $lecture->content_blocks,
                        'scorm_path' => $lecture->scorm_path,
                        'scorm_package_id' => $lecture->scorm_package_id,
                        'scorm_package_version_id' => $lecture->scorm_package_version_id,
                        'use_active_scorm_version' => $lecture->use_active_scorm_version,
                        'duration' => $lecture->duration,
                        'slides_status' => $lecture->slides_status,
                        'slides_path' => $lecture->slides_path,
                        'slides_source_path' => $lecture->slides_source_path,
                        'slide_count' => $lecture->slide_count,
                        'quiz_enabled' => false,
                    ]);
                }
            }

            $groupIds = $catalogModule->groups()->accessibleByTrainer($trainerId)->pluck('groups.id');
            $newModule->groups()->sync($groupIds);

            return $newModule;
        });
    }
}
