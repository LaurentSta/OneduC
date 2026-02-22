<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LearningAssetPath
{
    public static function lessonImportFolder(int $lectureId): string
    {
        $base = trim((string) config('learning_assets.lessons_scorm_base', 'modules/00_Lecons'), '/');

        return $base . '/lecture_' . $lectureId;
    }

    public static function resolveSectionVideoUrl(?string $raw): ?string
    {
        return self::resolveVideoUrl($raw);
    }

    public static function resolveModuleVideoUrl(?string $raw): ?string
    {
        return self::resolveVideoUrl($raw);
    }

    public static function resolveEvaluationIndexRelativePath(?string $folder): ?string
    {
        $folder = trim((string) $folder, '/');
        if ($folder === '') {
            return null;
        }

        $base = trim((string) config('learning_assets.evaluations_scorm_base', 'modules/evaluations/scorm'), '/');
        $primary = $base . '/' . $folder . '/res/index.html';
        if (self::existsInPublic($primary)) {
            return $primary;
        }

        $legacyBases = (array) config('learning_assets.evaluations_scorm_legacy', ['modules/scorm/01_evaluations']);
        foreach ($legacyBases as $legacyBase) {
            $legacyBase = trim((string) $legacyBase, '/');
            if ($legacyBase === '') {
                continue;
            }

            $legacy = $legacyBase . '/' . $folder . '/res/index.html';
            if (self::existsInPublic($legacy)) {
                return $legacy;
            }
        }

        return $primary;
    }

    private static function resolveVideoUrl(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        if (Str::startsWith($raw, ['http://', 'https://'])) {
            return $raw;
        }

        if (Str::startsWith($raw, '/')) {
            // Compatibilité : si /storage/... n'est pas publié dans public/storage
            // mais existe sur le disque Laravel "public", on passe par la route media.
            $normalized = ltrim($raw, '/');
            if (Str::startsWith($normalized, 'storage/')) {
                $relative = Str::after($normalized, 'storage/');
                $existsOnDisk = false;
                try {
                    $existsOnDisk = Storage::disk('public')->exists($relative);
                } catch (\Throwable) {
                    $existsOnDisk = false;
                }

                if (!self::existsInPublic($normalized) && $existsOnDisk) {
                    return route('media.storage', ['path' => $relative], false);
                }
            }

            return $raw;
        }

        $path = self::resolveVideoRelativePath($raw);

        return asset($path);
    }

    private static function resolveVideoRelativePath(string $relative): string
    {
        $relative = ltrim($relative, '/');

        // Cas déjà qualifié (ex: modules/videos/sections/...)
        if (self::existsInPublic($relative)) {
            return $relative;
        }

        $base = trim((string) config('learning_assets.videos_base', 'modules/videos'), '/');
        $primary = $base . '/' . $relative;
        if (self::existsInPublic($primary)) {
            return $primary;
        }

        $legacyBases = (array) config('learning_assets.videos_legacy', ['modules/scorm/02_videos']);
        foreach ($legacyBases as $legacyBase) {
            $legacyBase = trim((string) $legacyBase, '/');
            if ($legacyBase === '') {
                continue;
            }

            $legacy = $legacyBase . '/' . $relative;
            if (self::existsInPublic($legacy)) {
                return $legacy;
            }
        }

        return $primary;
    }

    private static function existsInPublic(string $relativePath): bool
    {
        return File::exists(public_path(trim($relativePath, '/')));
    }
}
