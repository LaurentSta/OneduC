<?php

namespace App\Support\LiveQuiz;

use App\Models\LiveQuizSession;
use App\Models\ModuleLecture;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptQuestion;
use App\Models\QuizQuestion;
use App\Services\QuizService;
use Illuminate\Support\Facades\Schema;

trait InteractsWithLiveQuizAttempts
{
    protected function createAttemptForSession(int $userId, ModuleLecture $lecture, LiveQuizSession $session): QuizAttempt
    {
        $session->loadMissing('sessionQuestions');

        $attempt = QuizAttempt::query()->create([
            'user_id' => $userId,
            'lecture_id' => $lecture->id,
            'started_at' => now(),
            'finished_at' => null,
            'total_questions' => (int) $session->total_questions,
            'score' => 0,
            'percent' => 0,
            'passed' => 0,
            'total_time_seconds' => 0,
        ]);

        foreach ($session->sessionQuestions as $sessionQuestion) {
            QuizAttemptQuestion::query()->create([
                'attempt_id' => $attempt->id,
                'question_id' => $sessionQuestion->question_id,
                'position' => $sessionQuestion->position,
                'time_seconds' => 0,
            ]);
        }

        return $attempt;
    }

    protected function gradeLiveAnswer(QuizQuestion $question, array $payload): array
    {
        return $this->quizService()->gradeAnswer($question, $payload);
    }

    protected function updateAttemptQuestionWithLiveAnswer(
        QuizAttemptQuestion $attemptQuestion,
        QuizQuestion $question,
        array $graded,
        int $durationSeconds
    ): void {
        $payload = [
            'is_correct' => (int) ($graded['is_correct'] ?? false),
            'answered_at' => now(),
            'time_seconds' => max(0, $durationSeconds),
        ];

        if ($this->attemptQuestionHasColumn('answer_option_ids')) {
            $payload['answer_option_ids'] = array_key_exists('answer_option_ids', $graded)
                && is_array($graded['answer_option_ids'])
                ? json_encode(array_values($graded['answer_option_ids']))
                : null;
        }

        if ($this->attemptQuestionHasColumn('given_answer')) {
            $payload['given_answer'] = $graded['given_answer'] ?? null;
        }

        $attemptQuestion->update($payload);
        $attemptQuestion->attempt?->touch();
    }

    protected function finalizeAttempt(QuizAttempt $attempt): void
    {
        $this->quizService()->finalizeAttempt($attempt);
    }

    protected function attemptQuestionHasColumn(string $column): bool
    {
        return Schema::hasColumn((new QuizAttemptQuestion())->getTable(), $column);
    }

    protected function quizService(): QuizService
    {
        return app(QuizService::class);
    }
}
