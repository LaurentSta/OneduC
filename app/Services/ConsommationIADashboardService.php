<?php

namespace App\Services;

use App\Domains\ModulesFormateur\Support\LimiteurBudgetTokensIA;
use App\Models\ConsommationIA;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ConsommationIADashboardService
{
    public function __construct(
        private readonly LimiteurBudgetTokensIA $limiteurBudget,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function resumeParFormateur(): Collection
    {
        return DB::table('consommations_ia')
            ->join('users', 'users.id', '=', 'consommations_ia.formateur_id')
            ->selectRaw('
                users.id as id,
                users.name as name,
                users.prenom as prenom,
                users.email as email,
                COUNT(*) as appels,
                SUM(consommations_ia.total_tokens) as total_tokens,
                MAX(consommations_ia.created_at) as derniere_generation
            ')
            ->groupBy('users.id', 'users.name', 'users.prenom', 'users.email')
            ->orderByDesc('total_tokens')
            ->get()
            ->map(fn ($row): array => [
                'id' => (int) $row->id,
                'nom' => trim((string) $row->prenom.' '.(string) $row->name) ?: (string) $row->email,
                'appels' => (int) $row->appels,
                'total_tokens' => (int) $row->total_tokens,
                'derniere_generation' => $row->derniere_generation,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function resumePourFormateur(int $formateurId): array
    {
        $totaux = $this->requetePourActeur($formateurId)
            ->selectRaw('
                COUNT(*) as appels,
                SUM(total_tokens) as total_tokens,
                SUM(prompt_tokens) as prompt_tokens,
                SUM(completion_tokens) as completion_tokens
            ')
            ->first();

        return [
            'totaux' => [
                'appels' => (int) ($totaux->appels ?? 0),
                'total_tokens' => (int) ($totaux->total_tokens ?? 0),
                'prompt_tokens' => (int) ($totaux->prompt_tokens ?? 0),
                'completion_tokens' => (int) ($totaux->completion_tokens ?? 0),
            ],
            'budget' => [
                'consomme_ce_mois' => $this->limiteurBudget->tokensConsommesCeMois($formateurId),
                'limite_mensuelle' => $this->limiteurBudget->limiteMensuelle($formateurId),
                'depasse' => $this->limiteurBudget->budgetDepasse($formateurId),
            ],
            'historique' => $this->requetePourActeur($formateurId)
                ->latest()
                ->paginate(20),
        ];
    }

    private function requetePourActeur(int $userId): Builder
    {
        $requete = ConsommationIA::query();
        $estAdministrateur = User::query()->whereKey($userId)->where('role', 'admin')->exists();

        return $estAdministrateur
            ? $requete->whereHas('formateur', fn (Builder $query) => $query->where('role', 'admin'))
            : $requete->where('formateur_id', $userId);
    }
}
