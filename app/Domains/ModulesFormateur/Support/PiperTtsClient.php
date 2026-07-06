<?php

namespace App\Domains\ModulesFormateur\Support;

use RuntimeException;
use Symfony\Component\Process\Process;

class PiperTtsClient
{
    public function synthesize(string $text): string
    {
        $binary = config('services.piper.binary');
        $model = config('services.piper.model');

        if (! is_file($binary) || ! is_executable($binary)) {
            throw new RuntimeException("Le moteur de synthèse vocale (Piper) n'est pas installé sur ce serveur.");
        }

        if (! is_file($model)) {
            throw new RuntimeException("Le modèle de voix Piper n'est pas installé sur ce serveur.");
        }

        $outputPath = tempnam(sys_get_temp_dir(), 'oneduc_tts_').'.wav';

        try {
            $process = new Process([$binary, '--model', $model, '--output_file', $outputPath]);
            $process->setInput($text);
            $process->setTimeout(120);
            $process->run();

            if (! $process->isSuccessful() || ! is_file($outputPath)) {
                throw new RuntimeException('La synthèse vocale a échoué.');
            }

            $audio = file_get_contents($outputPath);
            if ($audio === false || $audio === '') {
                throw new RuntimeException('La synthèse vocale a produit un fichier audio vide.');
            }

            return $audio;
        } finally {
            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
        }
    }
}
