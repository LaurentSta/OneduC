<?php

namespace App\Domains\Outils\Emargement\Support;

use Illuminate\Validation\ValidationException;

class SignatureImage
{
    private const MAX_DECODED_BYTES = 2 * 1024 * 1024;

    public function decode(string $payload): string
    {
        if (! preg_match('/^data:image\/png;base64,(.+)$/', $payload, $matches)) {
            throw ValidationException::withMessages([
                'signature' => 'La signature doit être une image PNG encodée en base64.',
            ]);
        }

        $binary = base64_decode($matches[1], true);

        if ($binary === false || $binary === '') {
            throw ValidationException::withMessages([
                'signature' => 'La signature transmise est invalide.',
            ]);
        }

        if (strlen($binary) > self::MAX_DECODED_BYTES) {
            throw ValidationException::withMessages([
                'signature' => 'La signature transmise est trop volumineuse.',
            ]);
        }

        return $binary;
    }
}
