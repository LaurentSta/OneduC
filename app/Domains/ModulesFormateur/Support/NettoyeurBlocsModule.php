<?php

namespace App\Domains\ModulesFormateur\Support;

use App\Models\Module;
use App\Models\ScormPackageVersion;
use App\Support\LearningAssetPath;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class NettoyeurBlocsModule
{
    public function sanitizeBlocks(?string $rawBlocksJson, int $moduleId): array
    {
        if (! $rawBlocksJson) {
            return [];
        }

        $decoded = json_decode($rawBlocksJson, true);
        if (! is_array($decoded)) {
            return [];
        }

        $validMediaIds = Media::query()
            ->where('model_type', Module::class)
            ->where('model_id', $moduleId)
            ->where('collection_name', 'lesson-images')
            ->pluck('id')
            ->flip();

        $sanitized = [];

        foreach (array_slice($decoded, 0, 100) as $block) {
            if (! is_array($block) || ! isset($block['type'])) {
                continue;
            }

            switch ($block['type']) {
                case 'text':
                    $html = $this->sanitizeHtml($block['html'] ?? null);
                    if ($html !== null) {
                        $sanitized[] = ['type' => 'text', 'html' => $html];
                    }
                    break;

                case 'image':
                    $mediaId = (int) ($block['media_id'] ?? 0);
                    if ($mediaId <= 0 || ! isset($validMediaIds[$mediaId])) {
                        break;
                    }
                    $sanitized[] = [
                        'type' => 'image',
                        'media_id' => $mediaId,
                        'caption' => Str::limit(strip_tags((string) ($block['caption'] ?? '')), 255, ''),
                    ];
                    break;

                case 'quote':
                    $text = trim(strip_tags((string) ($block['text'] ?? '')));
                    if ($text === '') {
                        break;
                    }
                    $sanitized[] = [
                        'type' => 'quote',
                        'text' => Str::limit($text, 1000, ''),
                        'source' => Str::limit(strip_tags((string) ($block['source'] ?? '')), 255, ''),
                    ];
                    break;

                case 'video':
                    $url = trim((string) ($block['url'] ?? ''));
                    if ($url === '' || ClassifieurUrlVideo::classify($url) === null) {
                        break;
                    }
                    $sanitized[] = [
                        'type' => 'video',
                        'url' => $url,
                        'caption' => Str::limit(strip_tags((string) ($block['caption'] ?? '')), 255, ''),
                    ];
                    break;

                case 'divider':
                    $sanitized[] = ['type' => 'divider'];
                    break;

                case 'scorm':
                    $key = (string) ($block['content_block_key'] ?? '');
                    $versionId = (int) ($block['scorm_package_version_id'] ?? 0);
                    if ($key === '' || ! preg_match('/^[A-Za-z0-9_-]{8,64}$/', $key) || $versionId <= 0) {
                        break;
                    }

                    $version = ScormPackageVersion::find($versionId);
                    if (! $version || $version->folder !== LearningAssetPath::lessonBlockScormFolder($moduleId, $key)) {
                        break;
                    }

                    $sanitized[] = [
                        'type' => 'scorm',
                        'content_block_key' => $key,
                        'scorm_package_version_id' => $versionId,
                    ];
                    break;
            }
        }

        return $sanitized;
    }

    private function sanitizeHtml(?string $html): ?string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return null;
        }

        $allowedTags = '<p><br><strong><b><em><i><u><s><ul><ol><li><h1><h2><h3><h4><blockquote><a><code><pre>';
        $clean = strip_tags($html, $allowedTags);

        $clean = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);

        $clean = preg_replace_callback(
            '/<a\s+([^>]*)>/i',
            function (array $matches) {
                $attrs = preg_replace('/href\s*=\s*("|\')\s*javascript:[^"\']*\1/i', 'href=$1#$1', $matches[1]);

                return '<a '.$attrs.'>';
            },
            $clean
        );

        return $clean;
    }
}
