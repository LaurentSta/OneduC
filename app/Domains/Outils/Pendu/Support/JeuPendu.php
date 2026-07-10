<?php

namespace App\Domains\Outils\Pendu\Support;

use Illuminate\Support\Str;
use stdClass;

class JeuPendu
{
    public const STATUS_EN_COURS = 'in_progress';

    public const STATUS_GAGNE = 'won';

    public const STATUS_PERDU = 'lost';

    public static function normaliserLettre(string $lettre): string
    {
        return Str::of($lettre)->substr(0, 1)->ascii()->upper()->toString();
    }

    /** @return array<int, string> */
    public static function lettresDuMot(string $mot): array
    {
        $lettres = [];

        foreach (mb_str_split($mot) as $caractere) {
            $ascii = Str::ascii($caractere);
            if (! ctype_alpha($ascii)) {
                continue;
            }

            $lettres[self::normaliserLettre($caractere)] = true;
        }

        return array_keys($lettres);
    }

    /** @return array<int, string> */
    public static function lettresProposees(stdClass $session): array
    {
        if (is_array($session->guessed_letters ?? null)) {
            return array_values($session->guessed_letters);
        }

        return array_values(json_decode((string) ($session->guessed_letters ?? '[]'), true) ?: []);
    }

    /** @return array<int, string> */
    public static function lettresIncorrectes(stdClass $session): array
    {
        return array_values(array_diff(
            self::lettresProposees($session),
            self::lettresDuMot((string) $session->word)
        ));
    }

    public static function statutApresProposition(stdClass $session, array $lettresProposees): string
    {
        if (array_diff(self::lettresDuMot((string) $session->word), $lettresProposees) === []) {
            return self::STATUS_GAGNE;
        }

        $lettresIncorrectes = array_diff($lettresProposees, self::lettresDuMot((string) $session->word));

        return count($lettresIncorrectes) >= (int) $session->max_attempts
            ? self::STATUS_PERDU
            : self::STATUS_EN_COURS;
    }

    /** @return array<string, mixed> */
    public static function etat(stdClass $session, int $participants): array
    {
        $lettresProposees = self::lettresProposees($session);
        $lettresIncorrectes = self::lettresIncorrectes($session);
        $revelerTout = (string) $session->status !== self::STATUS_EN_COURS;

        $lettresMasquees = array_map(function (string $caractere) use ($lettresProposees, $revelerTout): array {
            $estLettre = ctype_alpha(Str::ascii($caractere));
            if (! $estLettre) {
                return ['char' => $caractere, 'revealed' => true, 'is_letter' => false];
            }

            $revelee = $revelerTout || in_array(self::normaliserLettre($caractere), $lettresProposees, true);

            return ['char' => $revelee ? $caractere : '_', 'revealed' => $revelee, 'is_letter' => true];
        }, mb_str_split((string) $session->word));

        return [
            'title' => (string) $session->title,
            'letters' => $lettresMasquees,
            'guessed_letters' => $lettresProposees,
            'wrong_letters' => $lettresIncorrectes,
            'max_attempts' => (int) $session->max_attempts,
            'remaining_attempts' => max(0, (int) $session->max_attempts - count($lettresIncorrectes)),
            'status' => (string) $session->status,
            'is_active' => (bool) $session->is_active,
            'respondents' => $participants,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
