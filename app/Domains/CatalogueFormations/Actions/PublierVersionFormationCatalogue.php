<?php

namespace App\Domains\CatalogueFormations\Actions;

use App\Models\Module;
use App\Models\ModuleLecture;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublierVersionFormationCatalogue
{
    public function execute(Module $module): Module
    {
        if ($module->is_trainer_authored || $module->publication_state !== Module::PUBLICATION_DRAFT) {
            throw ValidationException::withMessages([
                'publication' => "Seul un brouillon du catalogue peut être publié.",
            ]);
        }

        $module->load(['sections.lectures.scormPackage.activeVersion', 'sections.lectures.scormPackageVersion']);
        $erreurs = $this->verifierCompletude($module);

        if ($erreurs !== []) {
            throw ValidationException::withMessages(['publication' => $erreurs]);
        }

        return DB::transaction(function () use ($module): Module {
            $anciennesVersionsPubliees = Module::query()
                ->where('catalogue_key', $module->catalogue_key)
                ->whereKeyNot($module->id)
                ->where('publication_state', Module::PUBLICATION_PUBLISHED)
                ->get();

            foreach ($anciennesVersionsPubliees as $ancienneVersion) {
                $ancienneVersion->forceFill([
                    'publication_state' => Module::PUBLICATION_ARCHIVED,
                    // Seules les versions encore épinglées doivent rester actives.
                    'status' => $ancienneVersion->groups()->exists(),
                ])->save();
            }

            foreach ($module->sections->flatMap->lectures as $lecture) {
                $this->figerVersionScorm($lecture);
            }

            $module->forceFill([
                'publication_state' => Module::PUBLICATION_PUBLISHED,
                'published_at' => now(),
                'status' => true,
            ])->save();

            return $module->fresh(['sections.lectures']);
        });
    }

    /**
     * @return array<int, string>
     */
    private function verifierCompletude(Module $module): array
    {
        $erreurs = [];

        if (blank($module->module_title)) {
            $erreurs[] = 'Le titre de la formation est obligatoire.';
        }

        if (! $module->category_id || ! $module->subcategory_id) {
            $erreurs[] = 'La catégorie et la sous-catégorie doivent être renseignées.';
        }

        if ($module->sections->isEmpty()) {
            $erreurs[] = 'Ajoutez au moins un chapitre.';
        }

        foreach ($module->sections as $section) {
            if ($section->lectures->isEmpty()) {
                $erreurs[] = 'Le chapitre « '.($section->section_title ?: 'Sans titre').' » ne contient aucune leçon.';
            }

            foreach ($section->lectures as $lecture) {
                if (! $this->leconContientDuContenu($lecture)) {
                    $erreurs[] = 'La leçon « '.($lecture->lecture_title ?: 'Sans titre').' » ne contient aucun contenu publiable.';
                }
            }
        }

        return array_values(array_unique($erreurs));
    }

    private function leconContientDuContenu(ModuleLecture $lecture): bool
    {
        return match ($lecture->content_type) {
            'blocks' => ! empty($lecture->content_blocks) || (bool) $lecture->quiz_enabled,
            'slides' => $lecture->slides_status === 'ready' && filled($lecture->slides_path),
            'scorm' => filled($lecture->scorm_path) || $lecture->resolveScormVersion() !== null,
            'html' => filled($lecture->html_content),
            default => filled($lecture->url) || filled($lecture->scorm_path),
        };
    }

    private function figerVersionScorm(ModuleLecture $lecture): void
    {
        if (! $lecture->scorm_package_id || ! $lecture->use_active_scorm_version) {
            return;
        }

        $version = $lecture->resolveScormVersion();
        if (! $version) {
            return;
        }

        $lecture->forceFill([
            'use_active_scorm_version' => false,
            'scorm_package_version_id' => $version->id,
        ])->save();
    }
}
