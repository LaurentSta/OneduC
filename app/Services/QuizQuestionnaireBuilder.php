<?php

namespace App\Services;

use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Construit des QuizQuestion/QuizOption indépendantes d'une leçon (lecture_id
 * null), rattachées à un questionnaire pour le quiz en direct group-only.
 * Volontairement distinct de App\Services\QuizQuestionBuilder (banque liée aux
 * leçons, avec média/CSV) pour ne pas toucher à ce service partagé et mature.
 */
class QuizQuestionnaireBuilder
{
    public function validatePayload(Request $request): array
    {
        return $request->validate([
            'question_text' => ['required', 'string', 'max:1000'],
            'type' => ['required', 'in:boolean,single,multiple,cloze'],
            'points' => ['nullable', 'integer', 'min:0', 'max:100'],
            'options' => ['nullable', 'array'],
            'options.*.text' => ['nullable', 'string', 'max:255'],
            'options.*.is_correct' => ['nullable'],
            'cloze_raw_text' => ['nullable', 'string', 'max:2000', 'required_if:type,cloze'],
            'cloze_blanks' => ['nullable', 'array'],
        ]);
    }

    public function createQuestion(QuizQuestionnaire $questionnaire, array $data, int $createdBy): QuizQuestion
    {
        $type = (string) $data['type'];

        if ($type === 'cloze') {
            $payload = $this->buildClozePayload($data);
            $options = [];
        } else {
            $options = $this->buildOptionsForType($type, $data['options'] ?? []);
            $this->assertOptionsAreValidForType($type, $options);
            $payload = null;
        }

        return DB::transaction(function () use ($questionnaire, $type, $data, $payload, $options, $createdBy) {
            $question = QuizQuestion::create([
                'lecture_id' => null,
                'questionnaire_id' => $questionnaire->id,
                'type' => $type,
                'question_text' => trim((string) $data['question_text']),
                'is_active' => true,
                'created_by' => $createdBy,
                'points' => (int) ($data['points'] ?? 1),
                'payload' => $payload,
            ]);

            foreach ($options as $index => $option) {
                QuizOption::create([
                    'question_id' => $question->id,
                    'option_text' => $option['text'],
                    'is_correct' => $option['is_correct'],
                    'position' => $index,
                ]);
            }

            return $question->fresh('options');
        });
    }

    public function deleteQuestion(QuizQuestion $question): void
    {
        DB::transaction(function () use ($question): void {
            $question->options()->delete();
            $question->delete();
        });
    }

    private function buildOptionsForType(string $type, array $rawOptions): array
    {
        if ($type === 'boolean') {
            $trueCorrect = (bool) ($rawOptions[0]['is_correct'] ?? false);
            $falseCorrect = (bool) ($rawOptions[1]['is_correct'] ?? false);

            if ($trueCorrect === $falseCorrect) {
                $trueCorrect = true;
                $falseCorrect = false;
            }

            return [
                ['text' => 'Vrai', 'is_correct' => $trueCorrect],
                ['text' => 'Faux', 'is_correct' => $falseCorrect],
            ];
        }

        return collect($rawOptions)
            ->map(fn ($option) => [
                'text' => trim((string) ($option['text'] ?? '')),
                'is_correct' => (bool) ($option['is_correct'] ?? false),
            ])
            ->filter(fn ($option) => $option['text'] !== '')
            ->values()
            ->all();
    }

    private function assertOptionsAreValidForType(string $type, array $options): void
    {
        if (count($options) < 2) {
            throw ValidationException::withMessages(['options' => 'Ajoutez au moins deux options.']);
        }

        $correctCount = collect($options)->where('is_correct', true)->count();

        if (in_array($type, ['single', 'boolean'], true) && $correctCount !== 1) {
            throw ValidationException::withMessages(['options' => 'Choisissez exactement une bonne réponse.']);
        }

        if ($type === 'multiple' && $correctCount < 1) {
            throw ValidationException::withMessages(['options' => 'Choisissez au moins une bonne réponse.']);
        }
    }

    private function buildClozePayload(array $data): array
    {
        $rawText = trim((string) ($data['cloze_raw_text'] ?? ''));
        preg_match_all('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/', $rawText, $matches);
        $keys = array_values(array_unique($matches[1] ?? []));

        if (empty($keys)) {
            throw ValidationException::withMessages([
                'cloze_raw_text' => 'Ajoutez au moins un trou au format {{cle}}.',
            ]);
        }

        $blanks = [];
        foreach ($keys as $key) {
            $rawAccepted = (string) data_get($data, "cloze_blanks.{$key}.accepted_answers", '');
            $accepted = collect(explode(',', $rawAccepted))
                ->map(fn ($value) => trim($value))
                ->filter()
                ->values()
                ->all();

            if (empty($accepted)) {
                throw ValidationException::withMessages([
                    "cloze_blanks.{$key}.accepted_answers" => "Indiquez au moins une réponse acceptée pour {$key}.",
                ]);
            }

            $blanks[$key] = [
                'accepted_answers' => $accepted,
                'points' => max(0, (int) data_get($data, "cloze_blanks.{$key}.points", 1)),
            ];
        }

        return [
            'raw_text' => $rawText,
            'blanks' => $blanks,
        ];
    }
}
