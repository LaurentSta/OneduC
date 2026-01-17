<?php

namespace App\Services\Scorm;

use App\Models\ScormPackage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use ZipArchive;
use RuntimeException;

class ScormImporter
{
    // Dans ScormImporter.php
public function importToFolder(UploadedFile $zipFile, string $targetPath): object
{
    $basePath = public_path($targetPath);
    File::ensureDirectoryExists($basePath);

    $zip = new ZipArchive();
    if ($zip->open($zipFile->getRealPath()) !== true) {
        throw new RuntimeException('Impossible d\'ouvrir le ZIP.');
    }

    $zip->extractTo($basePath);
    $zip->close();

    $relativeIndex = $this->findIndexPath($basePath);
    if (!$relativeIndex) {
        throw new RuntimeException('Index SCORM introuvable.');
    }

    $fullIndexPath = $basePath . DIRECTORY_SEPARATOR . $relativeIndex;

    // L'injection est maintenant automatique
    $this->injectApiScript($fullIndexPath);

    $slug = basename($targetPath);
    $package = ScormPackage::updateOrCreate(
        ['slug' => $slug],
        ['name' => str_replace('_', ' ', $slug)]
    );

    return (object) [
        'package_id' => $package->id,
        'relative_index_path' => $targetPath . '/' . $relativeIndex,
    ];
}

// Gardez la méthode injectApiScript telle quelle dans le service

    private function findIndexPath(string $basePath): ?string
    {
        if (File::exists($basePath . '/res/index.html')) return 'res/index.html';
        if (File::exists($basePath . '/index.html')) return 'index.html';
        return null;
    }

    private function injectApiScript(string $indexPath): void
    {
        $html = File::get($indexPath);
        if (!str_contains($html, 'scorm_core/js/API.js')) {
            $script = "\n<script src=\"/scorm_core/js/API.js\"></script>\n";
            $html = preg_replace('#</head>#i', $script . '</head>', $html, 1);
            File::put($indexPath, $html);
        }
    }
}