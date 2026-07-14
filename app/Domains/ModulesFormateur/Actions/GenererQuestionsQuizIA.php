<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Domains\ModulesFormateur\Support\GardeFouPromptIA;
use App\Domains\ModulesFormateur\Support\LimiteurBudgetTokensIA;
use App\Domains\ModulesFormateur\Support\LimiteurGenerationIA;
use App\Domains\ModulesFormateur\Support\MistralClient;
use App\Models\ModuleLecture;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Support\Str;
use RuntimeException;

class GenererQuestionsQuizIA
{
    private const MAX_SOURCE_CHARS = 18000;

    private const SYSTEM_PROMPT = <<<'PROMPT'
Tu es un assistant pédagogique qui génère des questions de quiz à partir du contenu d'une leçon.
Réponds UNIQUEMENT avec un objet JSON de la forme :
{"questions": [{"type": "single|multiple|boolean", "question_text": "...", "options": [{"text": "...", "is_correct": true|false}]}]}
Règles :
- Les types autorisés sont "single" (une seule bonne réponse), "multiple" (une ou plusieurs bonnes réponses) et "boolean" (Vrai/Faux, exactement 2 options : Vrai, Faux).
- Chaque question "single" ou "multiple" doit avoir entre 2 et 5 options, dont au moins une correcte.
- Base-toi uniquement sur le contenu fourni, sans inventer d'informations absentes.
- N'ajoute aucun texte hors de l'objet JSON.
PROMPT;

    public function __construct(
        private readonly MistralClient $mistral,
        private readonly GardeFouPromptIA $gardeFou,
        private readonly LimiteurGenerationIA $limiteur,
        private readonly LimiteurBudgetTokensIA $limiteurBudget,
    ) {}

    public function execute(ModuleLecture $lecture, int $count, int $trainerId): int
    {
        if ($this->limiteur->tropDeTentatives($trainerId, 'quiz')) {
            throw new RuntimeException('Limite de 3 générations IA par jour atteinte. Réessayez demain.');
        }

        if ($this->limiteurBudget->budgetDepasse($trainerId)) {
            throw new RuntimeException(
                'Vous avez atteint votre plafond mensuel de '.number_format($this->limiteurBudget->limiteMensuelle(), 0, ',', ' ').' tokens IA. Réessayez le mois prochain.'
            );
        }

        $sourceText = Str::limit($this->extraireTexte($lecture), self::MAX_SOURCE_CHARS, '');

        if (trim($sourceText) === '') {
            throw new RuntimeException("Cette leçon n'a pas encore de contenu texte à partir duquel générer des questions.");
        }

        $this->gardeFou->verifier($sourceText, $trainerId);
        $this->limiteur->enregistrerTentative($trainerId, 'quiz');

        $raw = $this->mistral->chat(
            self::SYSTEM_PROMPT,
            "Génère {$count} question(s) de quiz à partir du contenu de leçon suivant :\n\n".$sourceText,
            trainerId: $trainerId,
        );

        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || ! isset($decoded['questions']) || ! is_array($decoded['questions'])) {
            throw new RuntimeException("La réponse de l'IA n'a pas pu être interprétée.");
        }

        $created = 0;
        foreach ($decoded['questions'] as $questionData) {
            if ($this->creerQuestion($lecture, $questionData, $trainerId)) {
                $created++;
            }
        }

        if ($created === 0) {
            throw new RuntimeException("L'IA n'a renvoyé aucune question exploitable. Réessayez.");
        }

        return $created;
    }

    private function extraireTexte(ModuleLecture $lecture): string
    {
        $blocks = is_array($lecture->content_blocks) ? $lecture->content_blocks : [];

        return collect($blocks)
            ->filter(fn ($block) => is_array($block) && ($block['type'] ?? null) === 'text' && ! empty($block['html']))
            ->map(fn ($block) => trim(strip_tags((string) $block['html'])))
            ->filter()
            ->implode("\n\n");
    }

    private function creerQuestion(ModuleLecture $lecture, mixed $data, int $trainerId): bool
    {
        if (! is_array($data)) {
            return false;
        }

        $type = (string) ($data['type'] ?? '');
        $text = trim((string) ($data['question_text'] ?? ''));
        $options = is_array($data['options'] ?? null) ? array_values($data['options']) : [];

        if (! in_array($type, ['single', 'multiple', 'boolean'], true) || $text === '' || count($options) < 2) {
            return false;
        }

        $hasCorrect = collect($options)->contains(fn ($option) => ! empty($option['is_correct']));
        if (! $hasCorrect) {
            return false;
        }

        $question = QuizQuestion::create([
            'lecture_id' => $lecture->id,
            'type' => $type,
            'question_text' => Str::limit($text, 2000, ''),
            'is_active' => false,
            'created_by' => $trainerId,
        ]);

        foreach ($options as $index => $option) {
            $optionText = trim((string) ($option['text'] ?? ''));
            if ($optionText === '') {
                continue;
            }

            QuizOption::create([
                'question_id' => $question->id,
                'option_text' => Str::limit($optionText, 500, ''),
                'is_correct' => (bool) ($option['is_correct'] ?? false),
                'position' => $index + 1,
            ]);
        }

        return true;
    }
}
