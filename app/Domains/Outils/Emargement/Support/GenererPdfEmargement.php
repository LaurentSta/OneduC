<?php

namespace App\Domains\Outils\Emargement\Support;

use App\Models\Seance;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

class GenererPdfEmargement
{
    public function execute(Seance $seance): DomPdf
    {
        $seance->load(['group', 'formateur', 'presences' => fn ($q) => $q->orderBy('stagiaire_nom_snapshot')]);

        $creneauxLabels = [
            'matin' => 'Matin',
            'apres_midi' => 'Après-midi',
            'journee' => 'Journée complète',
            'soiree' => 'Soirée',
        ];

        $lignes = $seance->presences->map(fn ($presence) => [
            'nom' => $presence->stagiaire_nom_snapshot ?? optional($presence->user)->prenom.' '.optional($presence->user)->name,
            'statut' => $presence->statut,
            'motif_absence' => $presence->motif_absence,
            'signature' => $presence->signatureBase64,
        ]);

        return Pdf::loadView('formateur.emargement.pdf', [
            'seance' => $seance,
            'group' => $seance->group,
            'creneauLabel' => $creneauxLabels[$seance->creneau] ?? $seance->creneau,
            'lignes' => $lignes,
        ]);
    }
}
