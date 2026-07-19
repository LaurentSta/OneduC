<?php

namespace App\Domains\ModulesFormateur\Support;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

class LimiteurGenerationIA
{
    private const MAX_PAR_JOUR = 3;

    private const DECAY_SECONDS = 86400;

    public function tropDeTentatives(int $trainerId, string $type = 'texte'): bool
    {
        return RateLimiter::tooManyAttempts(
            $this->cle($trainerId, $type),
            $this->limiteQuotidienne($trainerId),
        );
    }

    public function enregistrerTentative(int $trainerId, string $type = 'texte'): void
    {
        RateLimiter::hit($this->cle($trainerId, $type), self::DECAY_SECONDS);
    }

    public function tentativesRestantes(int $trainerId, string $type = 'texte'): int
    {
        return RateLimiter::remaining(
            $this->cle($trainerId, $type),
            $this->limiteQuotidienne($trainerId),
        );
    }

    public function limiteQuotidienne(int $userId): int
    {
        return $this->estAdministrateur($userId)
            ? (int) config('services.mistral.admin_daily_generation_limit', 20)
            : self::MAX_PAR_JOUR;
    }

    private function cle(int $trainerId, string $type): string
    {
        if ($this->estAdministrateur($trainerId)) {
            return 'ia-generation-admin:'.$type;
        }

        return 'ia-generation-formateur:'.$type.':'.$trainerId;
    }

    private function estAdministrateur(int $userId): bool
    {
        return User::query()->whereKey($userId)->where('role', 'admin')->exists();
    }
}
