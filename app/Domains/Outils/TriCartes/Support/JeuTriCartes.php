<?php

namespace App\Domains\Outils\TriCartes\Support;

class JeuTriCartes
{
    /**
     * @param  array<int, int>  $categoriesCorrectesParCarte  card_id => correct_category_id
     * @param  array<int, int>  $placements  card_id => category_id choisi par le stagiaire
     * @return array{score: int, total: int, details: array<int, array{card_id: int, category_id: int, is_correct: bool}>}
     */
    public static function corriger(array $categoriesCorrectesParCarte, array $placements): array
    {
        $details = [];
        $score = 0;

        foreach ($categoriesCorrectesParCarte as $cardId => $correctCategoryId) {
            $categoryId = $placements[$cardId] ?? null;
            $estCorrecte = $categoryId !== null && (int) $categoryId === (int) $correctCategoryId;

            if ($estCorrecte) {
                $score++;
            }

            $details[] = [
                'card_id' => $cardId,
                'category_id' => (int) ($categoryId ?? 0),
                'is_correct' => $estCorrecte,
            ];
        }

        return [
            'score' => $score,
            'total' => count($categoriesCorrectesParCarte),
            'details' => $details,
        ];
    }
}
