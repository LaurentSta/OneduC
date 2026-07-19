<?php

namespace App\Domains\ModulesFormateur\Support;

use App\Models\ConsommationIA;
use App\Models\User;

class LimiteurBudgetTokensIA
{
    public function limiteMensuelle(?int $userId = null): int
    {
        $utilisateur = $userId ? User::query()->find($userId) : null;

        if ($utilisateur?->role === 'admin') {
            return (int) config('services.mistral.admin_monthly_token_limit', 2000000);
        }

        return (int) config('services.mistral.monthly_token_limit', 500000);
    }

    public function tokensConsommesCeMois(int $trainerId): int
    {
        $requete = ConsommationIA::query();

        if ($this->estAdministrateur($trainerId)) {
            $requete->whereHas('formateur', fn ($query) => $query->where('role', 'admin'));
        } else {
            $requete->where('formateur_id', $trainerId);
        }

        return (int) $requete
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total_tokens');
    }

    public function budgetDepasse(int $trainerId): bool
    {
        return $this->tokensConsommesCeMois($trainerId) >= $this->limiteMensuelle($trainerId);
    }

    private function estAdministrateur(int $userId): bool
    {
        return User::query()->whereKey($userId)->where('role', 'admin')->exists();
    }
}
