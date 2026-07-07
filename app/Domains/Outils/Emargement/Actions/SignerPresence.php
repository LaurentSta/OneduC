<?php

namespace App\Domains\Outils\Emargement\Actions;

use App\Domains\Outils\Emargement\Support\SignatureImage;
use App\Models\SeancePresence;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SignerPresence
{
    public function __construct(private readonly SignatureImage $signatureImage) {}

    public function execute(SeancePresence $presence, User $stagiaire, string $signatureBase64): SeancePresence
    {
        if ((int) $presence->user_id !== (int) $stagiaire->id) {
            abort(403);
        }

        if ($presence->seance->statut !== 'ouverte') {
            throw ValidationException::withMessages([
                'signature' => "Cette séance n'est pas ouverte à la signature.",
            ]);
        }

        if ($presence->statut === 'present') {
            throw ValidationException::withMessages([
                'signature' => 'Vous avez déjà signé cette séance.',
            ]);
        }

        $binary = $this->signatureImage->decode($signatureBase64);

        $presence->addMediaFromString($binary)
            ->usingFileName('signature.png')
            ->toMediaCollection('signature');

        $presence->update([
            'statut' => 'present',
            'signature_type' => 'auto',
            'updated_by' => $stagiaire->id,
        ]);

        return $presence;
    }
}
