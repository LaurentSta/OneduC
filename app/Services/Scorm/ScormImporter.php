<?php

namespace App\Services\Scorm;

use App\Models\ScormPackage;
use App\Models\ScormPackageVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use ZipArchive;
use RuntimeException;

class ScormImporter
{
    public function importToFolder(UploadedFile $zipFile, string $targetPath): object
    {
        $basePath = public_path($targetPath);
        File::ensureDirectoryExists($basePath);

        $zip = new ZipArchive();
        if ($zip->open($zipFile->getRealPath()) !== true) {
            throw new RuntimeException('Impossible d\'ouvrir le ZIP.');
        }

        if (!$zip->extractTo($basePath)) {
            $status = method_exists($zip, 'getStatusString') ? $zip->getStatusString() : 'Extraction impossible';
            $zip->close();
            throw new RuntimeException('Échec de décompression : ' . $status);
        }
        $zip->close();

        $relativeIndex = $this->findIndexPath($basePath);
        if (!$relativeIndex) {
            throw new RuntimeException('Index SCORM introuvable.');
        }

        $fullIndexPath = $basePath . DIRECTORY_SEPARATOR . $relativeIndex;
        $this->injectApiScript($fullIndexPath);

        // Package (slug stable = nom du dossier)
        $slug = basename($targetPath);

        $package = ScormPackage::updateOrCreate(
            ['slug' => $slug],
            ['name' => str_replace('_', ' ', $slug)]
        );

        // Version : vous voulez "écraser" => on force une version unique (1)
        $indexPath = $targetPath . '/' . $relativeIndex;

        $version = ScormPackageVersion::updateOrCreate(
            [
                'scorm_package_id' => $package->id,
                'version' => 1,
            ],
            [
                'folder' => $targetPath,
                'index_path' => $indexPath,
                'size_bytes' => (int) $zipFile->getSize(),
                'api_injected' => true,
                'imported_at' => now(),
            ]
        );

        // Optionnel : si vous voulez vraiment "pas d’historique"
        ScormPackageVersion::where('scorm_package_id', $package->id)
            ->where('id', '!=', $version->id)
            ->delete();

        // Active version = celle-ci
        $package->update(['active_version_id' => $version->id]);

        return (object) [
            'package_id' => $package->id,
            'version_id' => $version->id,
            'relative_index_path' => $indexPath,
        ];
    }

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
