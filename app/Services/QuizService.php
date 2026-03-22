<?php

namespace App\Services;

use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use Illuminate\Support\Str;

class QuizService
{
    public function gradeAnswer(QuizQuestion $question, array $payload): array
    {
        $type = (string) $question->type;

        if (in_array($type, ['single', 'boolean'], true)) {
            $selectedIds = [(int) ($payload['answer'] ?? 0)];
            $isCorrect = $this->selectedOptionsMatchCorrectAnswers($question, $selectedIds);

            return [
                'is_correct' => $isCorrect,
                'answer_option_ids' => $selectedIds,
                'given_answer' => null,
            ];
        }

        if ($type === 'multiple') {
            $selectedIds = collect($payload['answers'] ?? [])
                ->flatten()
                ->filter()
                ->map(fn ($value) => (int) $value)
                ->unique()
                ->values()
                ->all();

            return [
                'is_correct' => !empty($selectedIds)
                    && $this->selectedOptionsMatchCorrectAnswers($question, $selectedIds),
                'answer_option_ids' => $selectedIds,
                'given_answer' => null,
            ];
        }

        if ($type === 'cloze') {
            $givenAnswer = $this->gradeClozeAnswer($question, (array) ($payload['answers'] ?? []));

            return [
                'is_correct' => (bool) ($givenAnswer['is_correct'] ?? false),
                'answer_option_ids' => null,
                'given_answer' => $givenAnswer,
            ];
        }

        abort(422, 'Type de question non pris en charge.');
    }

    public function finalizeAttempt(QuizAttempt $attempt): void
    {
        $rows = $attempt->attemptQuestions()
            ->with('question')
            ->get();

        $earnedPoints = 0.0;
        $maxPoints = 0.0;

        foreach ($rows as $row) {
            $isCorrect = (bool) $row->is_correct;
            $type = (string) ($row->question->type ?? '');

            if ($type === 'cloze') {
                $given = is_array($row->given_answer ?? null) ? $row->given_answer : [];

                $rowScore = is_numeric($given['score'] ?? null)
                    ? (float) $given['score']
                    : ($isCorrect ? 1.0 : 0.0);

                $rowMax = is_numeric($given['max_score'] ?? null)
                    ? (float) $given['max_score']
                    : 1.0;

                if ($rowMax <= 0) {
                    $rowMax = 1.0;
                    $rowScore = $isCorrect ? 1.0 : 0.0;
                } else {
                    $rowScore = max(0.0, min($rowScore, $rowMax));
                }

                $earnedPoints += $rowScore;
                $maxPoints += $rowMax;
                continue;
            }

            $earnedPoints += $isCorrect ? 1.0 : 0.0;
            $maxPoints += 1.0;
        }

        if ($maxPoints <= 0) {
            $maxPoints = 1.0;
        }

        $percent = (int) round(($earnedPoints / $maxPoints) * 100);
        $totalTime = (int) $attempt->attemptQuestions()->sum('time_seconds');

        $attempt->forceFill([
            'score' => $percent,
            'percent' => $percent,
            'passed' => $percent >= 50 ? 1 : 0,
            'finished_at' => now(),
            'total_time_seconds' => $totalTime,
        ])->save();
    }

    public function gradeClozeAnswer(QuizQuestion $question, array $submittedAnswers): array
    {
        $payload = is_array($question->payload ?? null) ? $question->payload : [];
        $blanks = is_array($payload['blanks'] ?? null) ? $payload['blanks'] : [];

        if (empty($blanks)) {
            abort(422, 'Cette question a trous est mal configuree: aucun blank defini.');
        }

        $score = 0;
        $maxScore = 0;
        $details = [];
        $cleanAnswers = [];

        foreach ($blanks as $blankKey => $config) {
            $key = trim((string) $blankKey);
            if ($key === '') {
                continue;
            }

            $acceptedRaw = $config['accepted_answers'] ?? [];
            if (!is_array($acceptedRaw)) {
                $acceptedRaw = [$acceptedRaw];
            }

            $accepted = collect($acceptedRaw)
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->values()
                ->all();

            $normalizedAccepted = collect($accepted)
                ->map(fn ($value) => $this->normalizeClozeText($value))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $points = is_numeric($config['points'] ?? null) ? (int) $config['points'] : 1;
            $points = max(0, $points);
            $maxScore += $points;

            $answerRaw = $submittedAnswers[$key] ?? '';
            $answerText = is_scalar($answerRaw) ? trim((string) $answerRaw) : '';
            $answerNormalized = $this->normalizeClozeText($answerText);

            $cleanAnswers[$key] = $answerText;

            $isBlankCorrect = $answerNormalized !== '' && in_array($answerNormalized, $normalizedAccepted, true);
            $earned = $isBlankCorrect ? $points : 0;
            $score += $earned;

            $details[$key] = [
                'answer' => $answerText,
                'normalized_answer' => $answerNormalized,
                'accepted_answers' => $accepted,
                'is_correct' => $isBlankCorrect,
                'points' => $points,
                'earned_points' => $earned,
            ];
        }

        if ($maxScore <= 0) {
            $maxScore = 1;
        }

        $allCorrect = !empty($details)
            && collect($details)->every(fn ($blank) => (bool) ($blank['is_correct'] ?? false));

        return [
            'type' => 'cloze',
            'answers' => $cleanAnswers,
            'blanks' => $details,
            'score' => $score,
            'max_score' => $maxScore,
            'percent' => (int) round(($score / $maxScore) * 100),
            'is_correct' => $allCorrect,
        ];
    }

    private function selectedOptionsMatchCorrectAnswers(QuizQuestion $question, array $selectedIds): bool
    {
        $selected = collect($selectedIds)->map(fn ($value) => (int) $value)->values();
        $correct = $question->options
            ->where('is_correct', true)
            ->pluck('id')
            ->map(fn ($value) => (int) $value)
            ->values();

        return $selected->diff($correct)->isEmpty()
            && $correct->diff($selected)->isEmpty();
    }

    private function normalizeClozeText(?string $value): string
    {
        $out = trim((string) $value);
        if ($out === '') {
            return '';
        }

        $out = preg_replace('/\s+/u', ' ', $out) ?? $out;
        $out = Str::lower($out);

        if (class_exists(\Normalizer::class)) {
            $normalized = \Normalizer::normalize($out, \Normalizer::FORM_D);
            if (is_string($normalized) && $normalized !== '') {
                $out = preg_replace('/\p{Mn}+/u', '', $normalized) ?? $normalized;
            }
        } else {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $out);
            if (is_string($ascii) && $ascii !== '') {
                $out = Str::lower($ascii);
            }
        }

        return trim($out);
    }
}
