<?php

namespace App\Services\ScormV2;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ScormV2Importer
{
    /**
     * Importe un ZIP SCORM et retourne:
     * - scorm_folder: dossier racine public (ex: scorm/lecture_12)
     * - scorm_launch_path: chemin relatif à public/storage (ex: scorm/lecture_12/res/index.html)
     */
    public function importForLecture(int $lectureId, UploadedFile $zip): array
    {
        if (!$zip->isValid()) {
            throw new \RuntimeException('Fichier ZIP invalide.');
        }

        $ext = strtolower($zip->getClientOriginalExtension());
        if ($ext !== 'zip') {
            throw new \RuntimeException('Le fichier doit être un ZIP.');
        }

        $folder = "scorm/lecture_{$lectureId}";
        $disk = Storage::disk('public');
        $absTarget = $disk->path($folder);

        // On écrase l’ancien contenu pour repartir proprement
        if (File::exists($absTarget)) {
            File::deleteDirectory($absTarget);
        }
        File::makeDirectory($absTarget, 0755, true);

        // Extraction sécurisée
        $zipPath = $zip->getRealPath();
        $za = new ZipArchive();
        if ($za->open($zipPath) !== true) {
            throw new \RuntimeException('Impossible d’ouvrir l’archive ZIP.');
        }

        for ($i = 0; $i < $za->numFiles; $i++) {
            $entry = $za->getNameIndex($i);

            // bloque traversal / chemins absolus
            if (str_contains($entry, '..') || str_starts_with($entry, '/') || str_starts_with($entry, '\\')) {
                continue;
            }

            $dest = $absTarget . DIRECTORY_SEPARATOR . str_replace(['\\'], '/', $entry);

            // Dossier
            if (str_ends_with($entry, '/')) {
                File::makeDirectory($dest, 0755, true, true);
                continue;
            }

            // Fichier : crée le dossier parent
            File::makeDirectory(dirname($dest), 0755, true, true);

            $stream = $za->getStream($entry);
            if ($stream === false) {
                continue;
            }
            file_put_contents($dest, stream_get_contents($stream));
            fclose($stream);
        }

        $za->close();

        // Détection du launch
        $launchRelative = $this->detectLaunchFile($absTarget);

        if (!$launchRelative) {
            throw new \RuntimeException("Aucun fichier de lancement détecté (manifest/index/story).");
        }

        // Chemin relatif à public/storage
        $launchPath = $folder . '/' . ltrim($launchRelative, '/');

        return [
            'scorm_folder'      => $folder,
            'scorm_launch_path' => $launchPath,
        ];
    }

    private function detectLaunchFile(string $absTarget): ?string
    {
        // 1) SCORM : imsmanifest.xml -> trouver une ressource html
        $manifest = $absTarget . DIRECTORY_SEPARATOR . 'imsmanifest.xml';
        if (File::exists($manifest)) {
            $found = $this->launchFromManifest($manifest, $absTarget);
            if ($found) {
                return $found;
            }
        }

        // 2) Storyline : story.html souvent à la racine ou dans un sous-dossier
        $story = $this->findFirstCaseInsensitive($absTarget, ['story.html']);
        if ($story) {
            return $story;
        }

        // 3) index.html
        $index = $this->findFirstCaseInsensitive($absTarget, ['index.html', 'index_lms.html']);
        if ($index) {
            return $index;
        }

        // 4) fallback : premier html trouvé (rare)
        $html = collect(File::allFiles($absTarget))
            ->filter(fn($f) => strtolower($f->getExtension()) === 'html')
            ->sortBy(fn($f) => strlen($f->getRelativePathname()))
            ->first();

        return $html ? str_replace('\\', '/', $html->getRelativePathname()) : null;
    }

    private function launchFromManifest(string $manifestPath, string $absTarget): ?string
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($manifestPath);
        if (!$xml) return null;

        // namespaces
        $xml->registerXPathNamespace('ns', $xml->getNamespaces(true)[''] ?? '');
        // On prend le premier href HTML trouvé
        $nodes = $xml->xpath('//*[local-name()="resource"]/@href');
        if (!$nodes) return null;

        foreach ($nodes as $n) {
            $href = (string) $n;
            if (!$href) continue;

            $href = str_replace('\\', '/', $href);
            $abs = $absTarget . DIRECTORY_SEPARATOR . $href;

            if (File::exists($abs) && preg_match('/\.html?$/i', $href)) {
                return ltrim($href, '/');
            }
        }

        return null;
    }

    private function findFirstCaseInsensitive(string $absTarget, array $names): ?string
    {
        $names = array_map('strtolower', $names);

        foreach (File::allFiles($absTarget) as $file) {
            $rel = str_replace('\\', '/', $file->getRelativePathname());
            $base = strtolower(basename($rel));
            if (in_array($base, $names, true)) {
                return $rel;
            }
        }
        return null;
    }
}
