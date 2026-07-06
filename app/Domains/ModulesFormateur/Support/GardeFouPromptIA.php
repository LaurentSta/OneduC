<?php

namespace App\Domains\ModulesFormateur\Support;

use Illuminate\Support\Str;
use RuntimeException;

class GardeFouPromptIA
{
    /**
     * Catégories de modération Mistral bloquées. Volontairement restreint aux catégories
     * clairement inadaptées : `health`, `financial`, `law` et `pii` sont exclues car une
     * plateforme de formation professionnelle traite légitimement ces sujets (premiers
     * secours, gestion budgétaire, droit du travail, protection des données...).
     */
    private const CATEGORIES_BLOQUEES = [
        'sexual' => 'contenu à caractère sexuel',
        'hate_and_discrimination' => 'incitation à la haine ou discrimination',
        'violence_and_threats' => 'violence ou menaces',
        'dangerous' => 'contenu dangereux',
        'criminal' => 'contenu à caractère criminel',
        'selfharm' => 'automutilation',
        'jailbreaking' => "tentative de contournement des instructions de l'IA",
    ];

    public function __construct(
        private readonly MistralClient $mistral,
    ) {}

    public function verifier(string $texte, ?int $trainerId = null): void
    {
        $texte = trim($texte);
        if ($texte === '') {
            return;
        }

        $categories = $this->mistral->moderate(Str::limit($texte, 8000, ''), $trainerId);

        $declenchees = array_intersect_key(self::CATEGORIES_BLOQUEES, array_filter($categories));

        if ($declenchees !== []) {
            throw new RuntimeException(
                'Ce contenu a été refusé par les règles de modération ('.implode(', ', $declenchees).'). '
                .'Reformulez votre demande.'
            );
        }
    }
}
