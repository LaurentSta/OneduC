<?php

namespace App\Domains\ModulesFormateur\Support;

use App\Models\ConsommationIA;

class LimiteurBudgetTokensIA
{
    public function limiteMensuelle(): int
    {
        return (int) config('services.mistral.monthly_token_limit', 500000);
    }

    public function tokensConsommesCeMois(int $trainerId): int
    {
        return (int) ConsommationIA::query()
            ->where('formateur_id', $trainerId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total_tokens');
    }

    public function budgetDepasse(int $trainerId): bool
    {
        return $this->tokensConsommesCeMois($trainerId) >= $this->limiteMensuelle();
    }
}
