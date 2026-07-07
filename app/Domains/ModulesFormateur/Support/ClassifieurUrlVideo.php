<?php

namespace App\Domains\ModulesFormateur\Support;

class ClassifieurUrlVideo
{
    /**
     * Reconnait une URL YouTube, Vimeo ou de fichier video direct.
     * Retourne null si l'URL n'est pas reconnue (bloc alors rejete par le sanitizer).
     */
    public static function classify(string $url): ?array
    {
        if (! filter_var($url, FILTER_VALIDATE_URL) || ! preg_match('#^https?://#i', $url)) {
            return null;
        }

        if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([a-zA-Z0-9_-]{6,})#i', $url, $m)) {
            return ['kind' => 'youtube', 'embed_url' => "https://www.youtube.com/embed/{$m[1]}"];
        }

        if (preg_match('#vimeo\.com/(?:video/)?(\d+)#i', $url, $m)) {
            return ['kind' => 'vimeo', 'embed_url' => "https://player.vimeo.com/video/{$m[1]}"];
        }

        if (preg_match('#\.(mp4|webm|ogg)(\?[^\s]*)?$#i', $url)) {
            return ['kind' => 'file', 'embed_url' => $url];
        }

        return null;
    }
}
