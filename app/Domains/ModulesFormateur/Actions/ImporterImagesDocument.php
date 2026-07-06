<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Domains\ModulesFormateur\Support\ExtracteurTexteDocument;
use App\Models\Module;
use Illuminate\Http\UploadedFile;

class ImporterImagesDocument
{
    public function __construct(
        private readonly ExtracteurTexteDocument $extracteur,
        private readonly TeleverserImageModule $televerserImageModule,
    ) {}

    /**
     * Extrait les images d'un document et les rattache au module, sous forme de blocs
     * "image" prêts à insérer dans un contenu de leçon.
     *
     * @return array<int, array{type: string, media_id: int, caption: string}>
     */
    public function importer(UploadedFile $document, Module $module): array
    {
        $blocks = [];

        foreach ($this->extracteur->extractImages($document) as $image) {
            try {
                $fakeUpload = new UploadedFile($image['path'], basename($image['path']), $image['mime'], null, true);
                $media = $this->televerserImageModule->execute($module, $fakeUpload);
                $blocks[] = ['type' => 'image', 'media_id' => $media->id, 'caption' => ''];
            } finally {
                if (file_exists($image['path'])) {
                    unlink($image['path']);
                }
            }
        }

        return $blocks;
    }
}
