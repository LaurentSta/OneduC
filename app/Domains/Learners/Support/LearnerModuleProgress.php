<?php

namespace App\Domains\Learners\Support;

use App\Models\QuizAttempt;
use App\Models\QuizAttemptQuestion;
use App\Models\ScormInteraction;
use Illuminate\Support\Facades\DB;

class LearnerModuleProgress
{
    public function attachProgressAttributes($modules, int $userId): void
    {
        $scormAnswers = ScormInteraction::where('user_id', $userId)
            ->whereNotNull('lecture_id')
            ->select('lecture_id', DB::raw('COUNT(*) as count'))
            ->groupBy('lecture_id')
            ->pluck('count', 'lecture_id');

        $lectureIds = $modules
            ->flatMap(fn ($module) => $module->sections->flatMap->lectures)
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        [$quizAttempts, $quizAttemptAgg] = $this->latestQuizAttemptsData($lectureIds, $userId);

        foreach ($modules as $module) {
            $lectures = $module->sections->flatMap->lectures;
            $total = $lectures->count();
            $completed = 0;
            $started = false;

            foreach ($lectures as $lecture) {
                if ((bool) ($lecture->quiz_enabled ?? false)) {
                    $attempt = $quizAttempts->get($lecture->id);
                    $agg = $attempt ? $quizAttemptAgg->get($attempt->id) : null;
                    $status = $this->quizProgressStatus($attempt, $agg);

                    if ($status !== 'not_started') {
                        $started = true;
                    }
                    if ($status === 'completed') {
                        $completed++;
                    }

                    continue;
                }

                $scormCount = (int) ($scormAnswers[$lecture->id] ?? 0);
                if ($scormCount > 0) {
                    $started = true;
                    $completed++;
                }
            }

            $percent = $total > 0 ? (int) floor(($completed / $total) * 100) : 0;
            $status = $percent === 100 ? 'completed' : ($started ? 'in_progress' : 'not_started');

            $module->setAttribute('progress', $percent);
            $module->setAttribute('progression_percent', $percent);
            $module->setAttribute('progression_status', $status);
        }
    }

    public function latestQuizAttemptsData(array $lectureIds, int $userId): array
    {
        if (empty($lectureIds)) {
            return [collect(), collect()];
        }

        $attempts = QuizAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('lecture_id', $lectureIds)
            ->orderByDesc('finished_at')
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('lecture_id')
            ->map(function ($rows) {
                return $rows->sortByDesc(function ($attempt) {
                    return $attempt->finished_at?->timestamp
                        ?? $attempt->started_at?->timestamp
                        ?? $attempt->created_at?->timestamp
                        ?? 0;
                })->first();
            });

        $attemptIds = $attempts->filter()->pluck('id')->all();
        $attemptAgg = collect();

        if (! empty($attemptIds)) {
            $attemptAgg = QuizAttemptQuestion::query()
                ->select([
                    'attempt_id',
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN answered_at IS NOT NULL THEN 1 ELSE 0 END) as answered'),
                    DB::raw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct'),
                ])
                ->whereIn('attempt_id', $attemptIds)
                ->groupBy('attempt_id')
                ->get()
                ->keyBy('attempt_id');
        }

        return [$attempts, $attemptAgg];
    }

    public function quizProgressStatus(?QuizAttempt $attempt, mixed $agg): string
    {
        if (! $attempt) {
            return 'not_started';
        }

        $answered = (int) data_get($agg, 'answered', 0);
        $hasStarted = $answered > 0
            || ! is_null($attempt->started_at)
            || ! is_null($attempt->finished_at);

        if (! is_null($attempt->finished_at) && (bool) ($attempt->passed ?? false)) {
            return 'completed';
        }

        return $hasStarted ? 'incomplete' : 'not_started';
    }
}
