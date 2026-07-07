<?php

namespace App\Domains\Outils\Emargement\Actions;

use App\Models\Group;
use App\Models\Seance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreerSeance
{
    public function execute(Group $group, array $data, User $formateur): Seance
    {
        return DB::transaction(function () use ($group, $data, $formateur) {
            $seance = Seance::create([
                'group_id' => $group->id,
                'formateur_id' => $formateur->id,
                'date' => $data['date'],
                'creneau' => $data['creneau'],
                'heure_debut' => $data['heure_debut'] ?? null,
                'heure_fin' => $data['heure_fin'] ?? null,
                'titre' => $data['titre'] ?? null,
                'statut' => 'planifiee',
            ]);

            $stagiaires = $group->students()->get(['users.id', 'name', 'prenom']);

            foreach ($stagiaires as $stagiaire) {
                $seance->presences()->create([
                    'user_id' => $stagiaire->id,
                    'stagiaire_nom_snapshot' => trim(($stagiaire->prenom ?? '').' '.($stagiaire->name ?? '')),
                    'statut' => 'en_attente',
                ]);
            }

            return $seance;
        });
    }
}
