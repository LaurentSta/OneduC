<?php

namespace App\Domains\CatalogueFormations\Actions;

use App\Models\LessonResource;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class CreerVersionFormationCatalogue
{
    public function execute(Module $source, int $administrateurId): Module
    {
        if ($source->is_trainer_authored) {
            throw new RuntimeException("Une création personnelle de formateur doit être dupliquée dans le catalogue avant d'être versionnée.");
        }

        return $this->copier($source, $administrateurId, false);
    }

    public function dupliquerCreationFormateur(Module $source, int $administrateurId): Module
    {
        if (! $source->is_trainer_authored) {
            throw new RuntimeException("Seule une création personnelle de formateur peut être dupliquée par cette action.");
        }

        return $this->copier($source, $administrateurId, true);
    }

    public function dupliquerPourFormateur(Module $source, int $formateurId): Module
    {
        if ($source->is_trainer_authored) {
            throw new RuntimeException("Seule une formation du catalogue peut être copiée dans l’espace personnel d’un formateur.");
        }

        return $this->copier($source, $formateurId, false, true);
    }

    private function copier(
        Module $source,
        int $acteurId,
        bool $depuisCreationFormateur,
        bool $versCreationFormateur = false,
    ): Module
    {
        return DB::transaction(function () use ($source, $acteurId, $depuisCreationFormateur, $versCreationFormateur): Module {
            $source->load([
                'sections.lectures.objectives.competencies',
                'sections.lectures.quizQuestions.options',
                'sections.lectures.lessonResources',
            ]);

            $prochaineVersion = ($depuisCreationFormateur || $versCreationFormateur)
                ? 1
                : ((int) Module::query()
                    ->where('catalogue_key', $source->catalogue_key)
                    ->lockForUpdate()
                    ->max('version_number')) + 1;

            $version = $source->replicate();
            $version->forceFill([
                'module_name_slug' => Str::slug((string) ($source->module_name ?: $source->module_title))
                    .'-v'.$prochaineVersion.'-'.Str::lower(Str::random(6)),
                'formateur_id' => $versCreationFormateur ? $acteurId : $source->formateur_id,
                'created_by' => $acteurId,
                'is_trainer_authored' => $versCreationFormateur,
                'catalogue_key' => $versCreationFormateur
                    ? null
                    : ($depuisCreationFormateur
                        ? (string) Str::uuid()
                        : ($source->catalogue_key ?: (string) Str::uuid())),
                'version_number' => $prochaineVersion,
                'publication_state' => Module::PUBLICATION_DRAFT,
                'published_at' => null,
                'source_module_id' => $source->id,
                'status' => $versCreationFormateur,
            ]);
            $version->save();

            $version->forceFill([
                'module_image' => $this->copierFichierPublic(
                    $source->module_image,
                    'uploads/modules/images/versions/module_'.$version->id
                ),
                'header_image' => $this->copierFichierPublic(
                    $source->header_image,
                    'uploads/modules/headers/versions/module_'.$version->id
                ),
                'module_video' => $this->copierVideoModule($source, $version),
            ])->save();

            [$mediasParId, $urlsVideo] = $this->copierMedias($source, $version);

            foreach ($source->sections as $sectionSource) {
                $section = $sectionSource->replicate();
                $section->module_id = $version->id;
                $section->save();

                foreach ($sectionSource->lectures as $lectureSource) {
                    $lecture = $lectureSource->replicate();
                    $lecture->module_id = $version->id;
                    $lecture->section_id = $section->id;
                    $lecture->content_blocks = $this->remapperBlocs(
                        $lectureSource->content_blocks ?? [],
                        $mediasParId,
                        $urlsVideo,
                    );

                    if ($lectureSource->scorm_package_id) {
                        $versionScorm = $lectureSource->resolveScormVersion();
                        $lecture->use_active_scorm_version = false;
                        $lecture->scorm_package_version_id = $versionScorm?->id;
                    }

                    $lecture->save();
                    $this->copierSlides($lectureSource, $lecture);
                    $this->copierObjectifs($lectureSource, $lecture);
                    $this->copierQuestions($lectureSource, $lecture);
                    $this->copierRessources($lectureSource, $lecture, $version);
                }
            }

            return $version->fresh(['sections.lectures']);
        });
    }

    /**
     * @return array{0: array<int, int>, 1: array<string, string>}
     */
    private function copierMedias(Module $source, Module $version): array
    {
        $mediasParId = [];
        $urlsVideo = [];

        foreach ($source->media as $media) {
            $copie = $media->copy($version, $media->collection_name, $media->disk);
            $mediasParId[(int) $media->id] = (int) $copie->id;

            if ($media->collection_name === 'lesson-videos') {
                $urlsVideo[$media->getUrl()] = $copie->getUrl();
            }
        }

        return [$mediasParId, $urlsVideo];
    }

    private function remapperBlocs(array $blocs, array $mediasParId, array $urlsVideo): array
    {
        return collect($blocs)->map(function ($bloc) use ($mediasParId, $urlsVideo) {
            if (! is_array($bloc)) {
                return $bloc;
            }

            if (in_array($bloc['type'] ?? null, ['image', 'audio'], true)) {
                $ancienId = (int) ($bloc['media_id'] ?? 0);
                $bloc['media_id'] = $mediasParId[$ancienId] ?? null;
            }

            if (($bloc['type'] ?? null) === 'video') {
                $bloc['url'] = $urlsVideo[$bloc['url'] ?? ''] ?? ($bloc['url'] ?? '');
            }

            return $bloc;
        })->values()->all();
    }

    private function copierObjectifs(ModuleLecture $source, ModuleLecture $cible): void
    {
        foreach ($source->objectives as $objectifSource) {
            $objectif = $objectifSource->replicate();
            $objectif->lecture_id = $cible->id;
            $objectif->save();

            $competences = $objectifSource->competencies
                ->mapWithKeys(fn ($competence) => [
                    $competence->id => ['position' => (int) ($competence->pivot->position ?? 0)],
                ])
                ->all();
            $objectif->competencies()->sync($competences);
        }
    }

    private function copierQuestions(ModuleLecture $source, ModuleLecture $cible): void
    {
        foreach ($source->quizQuestions as $questionSource) {
            $question = $questionSource->replicate();
            $question->lecture_id = $cible->id;
            $question->image_path = $this->copierMediaQuestion(
                $questionSource->image_path,
                $cible,
            );
            $question->audio_path = $this->copierMediaQuestion(
                $questionSource->audio_path,
                $cible,
            );
            $question->save();

            foreach ($questionSource->options as $optionSource) {
                $option = $optionSource->replicate();
                $option->question_id = $question->id;
                $option->save();
            }
        }
    }

    private function copierMediaQuestion(?string $chemin, ModuleLecture $lecture): ?string
    {
        return $this->copierFichierPublic($chemin, 'quiz/questions/lecture_'.$lecture->id);
    }

    private function copierRessources(ModuleLecture $source, ModuleLecture $cible, Module $module): void
    {
        foreach ($source->lessonResources as $ressourceSource) {
            $chemin = $this->copierFichierPublic(
                $ressourceSource->file_path,
                'module-resources/module_'.$module->id,
            );

            $ressource = $ressourceSource->replicate();
            $ressource->module_id = $module->id;
            $ressource->lecture_id = $cible->id;
            $ressource->file_path = $chemin ?: $ressourceSource->file_path;
            $ressource->save();
        }
    }

    private function copierSlides(ModuleLecture $source, ModuleLecture $cible): void
    {
        $public = Storage::disk('public');
        if ($source->slides_path && $public->exists($source->slides_path)) {
            $nouveauDossier = 'slides/lecture_'.$cible->id;
            $public->deleteDirectory($nouveauDossier);
            $public->makeDirectory($nouveauDossier);

            foreach ($public->allFiles($source->slides_path) as $fichier) {
                $relatif = Str::after($fichier, trim($source->slides_path, '/').'/');
                $public->copy($fichier, $nouveauDossier.'/'.$relatif);
            }

            $cible->slides_path = $nouveauDossier;
        }

        $local = Storage::disk('local');
        if ($source->slides_source_path && $local->exists($source->slides_source_path)) {
            $extension = pathinfo($source->slides_source_path, PATHINFO_EXTENSION);
            $nouvelleSource = 'slides/sources/lecture_'.$cible->id.'/source_'.Str::random(8)
                .($extension ? '.'.$extension : '');
            $local->makeDirectory('slides/sources/lecture_'.$cible->id);
            $local->copy($source->slides_source_path, $nouvelleSource);
            $cible->slides_source_path = $nouvelleSource;
        }

        if ($cible->isDirty(['slides_path', 'slides_source_path'])) {
            $cible->save();
        }
    }

    private function copierVideoModule(Module $source, Module $cible): ?string
    {
        $cheminSource = trim((string) $source->module_video);
        if ($cheminSource === '') {
            return null;
        }

        if (Str::startsWith($cheminSource, ['http://', 'https://', '//'])) {
            return $cheminSource;
        }

        $videosBase = trim((string) config('learning_assets.videos_base', 'modules/videos'), '/');
        $copie = $this->copierFichierPublic(
            $cheminSource,
            $videosBase.'/modules/module_'.$cible->id,
        );

        if ($copie === null || $copie === $cheminSource) {
            return $copie;
        }

        return route('media.storage', ['path' => $copie], false);
    }

    private function copierFichierPublic(?string $chemin, string $dossier): ?string
    {
        $chemin = trim((string) $chemin);
        if ($chemin === '') {
            return null;
        }

        $normalise = ltrim($chemin, '/');
        if (Str::startsWith($normalise, 'storage/')) {
            $normalise = Str::after($normalise, 'storage/');
        } elseif (Str::startsWith($normalise, 'media/storage/')) {
            $normalise = Str::after($normalise, 'media/storage/');
        }

        $disque = Storage::disk('public');
        if (! $disque->exists($normalise)) {
            return $chemin;
        }

        $nom = Str::random(8).'_'.basename($normalise);
        $destination = trim($dossier, '/').'/'.$nom;
        $disque->makeDirectory(trim($dossier, '/'));
        $disque->copy($normalise, $destination);

        return $destination;
    }
}
