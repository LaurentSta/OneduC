<?php

namespace App\Services;

use App\Domains\ModulesFormateur\Support\GardeFouPromptIA;
use App\Domains\ModulesFormateur\Support\LimiteurBudgetTokensIA;
use App\Domains\ModulesFormateur\Support\LimiteurGenerationIA;
use App\Domains\ModulesFormateur\Support\MistralClient;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionnaire;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Génère des questions à partir d'un sujet libre tapé par le formateur, pour
 * un questionnaire (sans leçon source). Réutilise les mêmes garde-fous et le
 * même client Mistral que App\Domains\ModulesFormateur\Actions\GenererQuestionsQuizIA
 * (banque de questions liée aux leçons), sans toucher à cette classe.
 */
class QuizQuestionnaireAIGenerator
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
Tu es un assistant pédagogique qui génère des questions de quiz à partir d'un sujet donné par le formateur.
Réponds UNIQUEMENT avec un objet JSON de la forme :
{"questions": [{"type": "single|multiple|boolean", "question_text": "...", "options": [{"text": "...", "is_correct": true|false}]}]}
Règles :
- Les types autorisés sont "single" (une seule bonne réponse), "multiple" (une ou plusieurs bonnes réponses) et "boolean" (Vrai/Faux, exactement 2 options : Vrai, Faux).
- Chaque question "single" ou "multiple" doit avoir entre 2 et 5 options, dont au moins une correcte.
- Formule chaque question autour d'une seule idée, sans ambiguïté. Évite les tournures négatives piégeuses (ex : "Lequel de ces éléments n'est PAS...") sauf si le mot-clé négatif est mis en évidence.
- Les mauvaises réponses (distracteurs) doivent être plausibles et de longueur comparable à la bonne réponse — n'en fais pas systématiquement la plus longue ou la plus détaillée.
- N'ajoute aucun texte hors de l'objet JSON.
PROMPT;

    public function __construct(
        private readonly MistralClient $mistral,
        private readonly GardeFouPromptIA $gardeFou,
        private readonly LimiteurGenerationIA $limiteur,
        private readonly LimiteurBudgetTokensIA $limiteurBudget,
    ) {}

    public function execute(QuizQuestionnaire $questionnaire, string $topic, int $count, int $trainerId): int
    {
        if ($this->limiteur->tropDeTentatives($trainerId, 'quiz')) {
            throw new RuntimeException('Limite de '.$this->limiteur->limiteQuotidienne($trainerId).' générations IA par jour atteinte. Réessayez demain.');
        }

        if ($this->limiteurBudget->budgetDepasse($trainerId)) {
            throw new RuntimeException(
                'Vous avez atteint votre plafond mensuel de '.number_format($this->limiteurBudget->limiteMensuelle($trainerId), 0, ',', ' ').' tokens IA. Réessayez le mois prochain.'
            );
        }

        $topic = trim($topic);
        if ($topic === '') {
            throw new RuntimeException('Indiquez un sujet pour générer des questions.');
        }

        $this->gardeFou->verifier($topic, $trainerId);
        $this->limiteur->enregistrerTentative($trainerId, 'quiz');

        $raw = $this->mistral->chat(
            self::SYSTEM_PROMPT,
            "Génère {$count} question(s) de quiz sur le sujet suivant : ".$topic,
            trainerId: $trainerId,
        );

        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || ! isset($decoded['questions']) || ! is_array($decoded['questions'])) {
            throw new RuntimeException("La réponse de l'IA n'a pas pu être interprétée.");
        }

        $created = 0;
        foreach ($decoded['questions'] as $questionData) {
            if ($this->creerQuestion($questionnaire, $questionData, $trainerId)) {
                $created++;
            }
        }

        if ($created === 0) {
            throw new RuntimeException("L'IA n'a renvoyé aucune question exploitable. Réessayez.");
        }

        return $created;
    }

    private function creerQuestion(QuizQuestionnaire $questionnaire, mixed $data, int $trainerId): bool
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
            'lecture_id' => null,
            'questionnaire_id' => $questionnaire->id,
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
