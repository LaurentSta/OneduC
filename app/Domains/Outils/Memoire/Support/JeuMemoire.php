<?php

namespace App\Domains\Outils\Memoire\Support;

class JeuMemoire
{
    /**
     * @param  array<int, array{a: string, b: string}>  $paires
     * @return array<int, array{pair_index: int, side: string, text: string}>
     */
    public static function construireJeu(array $paires): array
    {
        $cartes = [];

        foreach (array_values($paires) as $index => $paire) {
            $cartes[] = ['pair_index' => $index, 'side' => 'a', 'text' => (string) $paire['a']];
            $cartes[] = ['pair_index' => $index, 'side' => 'b', 'text' => (string) $paire['b']];
        }

        shuffle($cartes);

        return $cartes;
    }

    public static function calculerErreurs(int $coups, int $nombrePaires): int
    {
        return max(0, $coups - $nombrePaires);
    }
}
