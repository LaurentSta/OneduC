<?php

namespace App\Domains\Outils\Emargement\Actions;

use App\Models\Seance;

class ClorerSeance
{
    public function execute(Seance $seance): Seance
    {
        $seance->update([
            'statut' => 'cloturee',
            'closed_at' => now(),
        ]);

        return $seance;
    }
}
