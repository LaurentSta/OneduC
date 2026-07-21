<?php

namespace App\Services;

use App\Models\PollQuestionnaire;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PollQuestionnaireBuilder
{
    public function validatePayload(Request $request): array
    {
        return $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'choices' => ['required', 'array', 'min:2', 'max:5'],
            'choices.*' => ['required', 'string', 'max:200'],
        ]);
    }

    public function addQuestion(PollQuestionnaire $questionnaire, array $data): void
    {
        $choices = collect($data['choices'])
            ->map(fn ($choice) => trim((string) $choice))
            ->filter()
            ->values()
            ->all();

        if (count($choices) < 2) {
            throw ValidationException::withMessages(['choices' => 'Ajoutez au moins deux choix de réponse.']);
        }

        $questions = $questionnaire->questions ?? [];
        $questions[] = [
            'question' => trim((string) $data['question']),
            'choices' => $choices,
        ];

        $questionnaire->update(['questions' => array_values($questions)]);
    }

    public function removeQuestion(PollQuestionnaire $questionnaire, int $index): void
    {
        $questions = $questionnaire->questions ?? [];

        abort_unless(array_key_exists($index, $questions), 404);

        unset($questions[$index]);
        $questionnaire->update(['questions' => array_values($questions)]);
    }
}
