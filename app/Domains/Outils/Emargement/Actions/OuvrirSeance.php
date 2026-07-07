<?php

namespace App\Domains\Outils\Emargement\Actions;

use App\Models\Seance;

class OuvrirSeance
{
    public function execute(Seance $seance): Seance
    {
        $seance->update([
            'statut' => 'ouverte',
            'opened_at' => now(),
        ]);

        return $seance;
    }
}
