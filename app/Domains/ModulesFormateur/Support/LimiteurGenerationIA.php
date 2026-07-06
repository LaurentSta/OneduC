<?php

namespace App\Domains\ModulesFormateur\Support;

use Illuminate\Support\Facades\RateLimiter;

class LimiteurGenerationIA
{
    private const MAX_PAR_JOUR = 3;

    private const DECAY_SECONDS = 86400;

    public function tropDeTentatives(int $trainerId, string $type = 'texte'): bool
    {
        return RateLimiter::tooManyAttempts($this->cle($trainerId, $type), self::MAX_PAR_JOUR);
    }

    public function enregistrerTentative(int $trainerId, string $type = 'texte'): void
    {
        RateLimiter::hit($this->cle($trainerId, $type), self::DECAY_SECONDS);
    }

    public function tentativesRestantes(int $trainerId, string $type = 'texte'): int
    {
        return RateLimiter::remaining($this->cle($trainerId, $type), self::MAX_PAR_JOUR);
    }

    private function cle(int $trainerId, string $type): string
    {
        return 'ia-generation-formateur:'.$type.':'.$trainerId;
    }
}
