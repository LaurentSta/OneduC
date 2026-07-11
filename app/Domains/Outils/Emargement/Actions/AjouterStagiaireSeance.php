<?php

namespace App\Domains\Outils\Emargement\Actions;

use App\Models\Seance;
use App\Models\User;

class AjouterStagiaireSeance
{
    public function execute(Seance $seance, User $stagiaire): void
    {
        if ($seance->presences()->where('user_id', $stagiaire->id)->exists()) {
            return;
        }

        $seance->presences()->create([
            'user_id' => $stagiaire->id,
            'stagiaire_nom_snapshot' => trim(($stagiaire->prenom ?? '').' '.($stagiaire->name ?? '')),
            'statut' => 'en_attente',
        ]);
    }
}
