<?php

namespace App\Domains\Outils\Emargement\Actions;

use App\Domains\Outils\Emargement\Support\SignatureImage;
use App\Models\SeancePresence;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CorrigerPresence
{
    public function __construct(private readonly SignatureImage $signatureImage) {}

    public function execute(
        SeancePresence $presence,
        User $formateur,
        string $statut,
        ?string $signatureBase64,
        ?string $motifAbsence,
    ): SeancePresence {
        if ($statut === 'present') {
            if (! $signatureBase64) {
                throw ValidationException::withMessages([
                    'signature' => 'Une signature est requise pour marquer ce stagiaire présent.',
                ]);
            }

            $binary = $this->signatureImage->decode($signatureBase64);

            $presence->addMediaFromString($binary)
                ->usingFileName('signature-formateur.png')
                ->toMediaCollection('signature');

            $presence->update([
                'statut' => 'present',
                'signature_type' => 'formateur',
                'motif_absence' => null,
                'updated_by' => $formateur->id,
            ]);

            return $presence;
        }

        $presence->clearMediaCollection('signature');

        $presence->update([
            'statut' => 'absent',
            'signature_type' => null,
            'motif_absence' => $motifAbsence,
            'updated_by' => $formateur->id,
        ]);

        return $presence;
    }
}
