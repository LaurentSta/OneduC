<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Domains\ModulesFormateur\Support\LimiteurGenerationIA;
use App\Domains\ModulesFormateur\Support\PiperTtsClient;
use App\Models\ModuleLecture;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class GenererAudioLecon
{
    public function __construct(
        private readonly PiperTtsClient $piper,
        private readonly LimiteurGenerationIA $limiteur,
    ) {}

    public function execute(ModuleLecture $lecture, int $trainerId): Media
    {
        if ($this->limiteur->tropDeTentatives($trainerId, 'audio')) {
            throw new RuntimeException('Limite de '.$this->limiteur->limiteQuotidienne($trainerId).' générations audio par jour atteinte. Réessayez demain.');
        }

        $texte = $this->extraireTexteLisible($lecture);
        if ($texte === '') {
            throw new RuntimeException('Cette leçon ne contient pas de texte à lire (blocs image/vidéo/SCORM ignorés).');
        }

        $this->limiteur->enregistrerTentative($trainerId, 'audio');

        $audio = $this->piper->synthesize($texte);

        return $lecture->module->addMediaFromString($audio)
            ->usingFileName('lecon-'.$lecture->id.'-'.now()->timestamp.'.wav')
            ->toMediaCollection('lesson-audios');
    }

    private function extraireTexteLisible(ModuleLecture $lecture): string
    {
        $morceaux = [];

        foreach ((array) ($lecture->content_blocks ?? []) as $block) {
            if (! is_array($block)) {
                continue;
            }

            match ($block['type'] ?? null) {
                'text' => $morceaux[] = trim(strip_tags((string) ($block['html'] ?? ''))),
                'quote' => $morceaux[] = trim((string) ($block['text'] ?? '')),
                default => null,
            };
        }

        return trim(implode(".\n", array_filter($morceaux, fn ($m) => $m !== '')));
    }
}
