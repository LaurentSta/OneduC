<?php

namespace App\Services\Scorm;

use App\Models\ScormPackage;
use App\Models\ScormPackageVersion;
use DOMDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

class ScormImporter
{
    /**
     * Importe un package SCORM, injecte l'API runtime et active une version unique.
     */
    public function importToFolder(UploadedFile $zipFile, string $targetPath): object
    {
        $basePath = public_path($targetPath);
        File::ensureDirectoryExists($basePath);
        $importedAt = now();
        $realZipPath = $zipFile->getRealPath();
        if (!$realZipPath) {
            throw new RuntimeException('Fichier ZIP introuvable après upload.');
        }
        $zipHash = hash_file('sha256', $realZipPath) ?: null;

        $zip = new ZipArchive();
        if ($zip->open($realZipPath) !== true) {
            throw new RuntimeException('Impossible d\'ouvrir le ZIP.');
        }

        try {
            $this->safeExtract($zip, $basePath);
        } finally {
            $zip->close();
        }

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
                'imported_at' => $importedAt,
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
            'imported_at' => $importedAt->toIso8601String(),
            'zip_hash' => $zipHash,
            'cache_token' => (string) $importedAt->timestamp,
        ];
    }

    /**
     * Détecte le point d'entrée du package à partir du manifest puis fallback.
     */
    private function findIndexPath(string $basePath): ?string
    {
        // 1) Chercher imsmanifest.xml (même s’il est dans un sous-dossier)
        $manifest = collect(File::allFiles($basePath))
            ->first(fn ($f) => strtolower($f->getFilename()) === 'imsmanifest.xml');

        if (!$manifest) {
            return null;
        }

        $manifestDir = $manifest->getPathname();
        $manifestXml = File::get($manifestDir);

        // 2) Lire le href du manifest (resource href)
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;

        // évite les warnings XML en production
        libxml_use_internal_errors(true);
        $dom->loadXML($manifestXml);
        libxml_clear_errors();

        $resources = $dom->getElementsByTagName('resource');

        foreach ($resources as $res) {
            if ($res->hasAttribute('href')) {
                $href = ltrim($res->getAttribute('href'), '/');

                // Le href est relatif au dossier du manifest
                $abs = dirname($manifestDir) . DIRECTORY_SEPARATOR . $href;

                if (File::exists($abs)) {
                    // Retourner un chemin relatif à $basePath
                    $rel = str_replace($basePath . DIRECTORY_SEPARATOR, '', $abs);

                    return str_replace('\\', '/', $rel);
                }
            }
        }

        // 3) Solution de repli (cas simples)
        if (File::exists($basePath . '/res/index.html')) {
            return 'res/index.html';
        }
        if (File::exists($basePath . '/index_lms.html')) {
            return 'index_lms.html';
        }
        if (File::exists($basePath . '/index.html')) {
            return 'index.html';
        }

        return null;
    }

    /**
     * Ajoute le runtime SCORM si absent du head.
     */
    private function injectApiScript(string $indexPath): void
    {
        $html = File::get($indexPath);
        if (!str_contains($html, 'scorm_core/js/API.js')) {
            $script = "\n<script src=\"/scorm_core/js/API.js\"></script>\n";
            $html = preg_replace('#</head>#i', $script . '</head>', $html, 1);
            File::put($indexPath, $html);
        }
    }

    /**
     * Extrait le ZIP avec protections contre le path traversal.
     */
    private function safeExtract(ZipArchive $zip, string $basePath): void
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            if ($name === false) {
                continue;
            }

            // Normalisation des séparateurs pour appliquer les contrôles de sécurité.
            $name = str_replace('\\', '/', $name);

            // Refuser chemins absolus et traversée de dossiers.
            if (str_starts_with($name, '/') || str_contains($name, '../')) {
                throw new RuntimeException('ZIP invalide : chemin non autorisé détecté.');
            }

            $dest = $basePath . DIRECTORY_SEPARATOR . $name;

            // Répertoires.
            if (str_ends_with($name, '/')) {
                File::ensureDirectoryExists($dest);

                continue;
            }

            // Assurer le dossier parent.
            File::ensureDirectoryExists(dirname($dest));

            // Écrire le fichier.
            $stream = $zip->getStream($name);
            if ($stream === false) {
                throw new RuntimeException('Extraction impossible : ' . $name);
            }

            $contents = stream_get_contents($stream);
            fclose($stream);

            File::put($dest, $contents);
        }
    }
}
