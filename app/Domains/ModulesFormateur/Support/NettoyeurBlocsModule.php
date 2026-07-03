<?php

namespace App\Domains\ModulesFormateur\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $allowedImagePrefix = "modules_formateur/module_{$moduleId}/images/";
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
                    $path = (string) ($block['path'] ?? '');
                    if ($path === '' || ! str_starts_with($path, $allowedImagePrefix) || ! Storage::disk('public')->exists($path)) {
                        break;
                    }
                    $sanitized[] = [
                        'type' => 'image',
                        'path' => $path,
                        'caption' => Str::limit(strip_tags((string) ($block['caption'] ?? '')), 255, ''),
                    ];
                    break;

                case 'list':
                    $style = ($block['style'] ?? 'bullet') === 'numbered' ? 'numbered' : 'bullet';
                    $items = collect($block['items'] ?? [])
                        ->take(30)
                        ->map(fn ($item) => Str::limit(strip_tags((string) $item), 500, ''))
                        ->filter(fn ($item) => $item !== '')
                        ->values()
                        ->all();

                    if (! empty($items)) {
                        $sanitized[] = ['type' => 'list', 'style' => $style, 'items' => $items];
                    }
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

                case 'divider':
                    $sanitized[] = ['type' => 'divider'];
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
