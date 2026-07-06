<?php

namespace App\Domains\ModulesFormateur\Support;

use Illuminate\Support\Facades\RateLimiter;

class LimiteurGenerationIA
{
    private const MAX_PAR_JOUR = 3;

    private const DECAY_SECONDS = 86400;

    public function tropDeTentatives(int $trainerId): bool
    {
        return RateLimiter::tooManyAttempts($this->cle($trainerId), self::MAX_PAR_JOUR);
    }

    public function enregistrerTentative(int $trainerId): void
    {
        RateLimiter::hit($this->cle($trainerId), self::DECAY_SECONDS);
    }

    public function tentativesRestantes(int $trainerId): int
    {
        return RateLimiter::remaining($this->cle($trainerId), self::MAX_PAR_JOUR);
    }

    private function cle(int $trainerId): string
    {
        return 'ia-generation-formateur:'.$trainerId;
    }
}
