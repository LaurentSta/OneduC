<?php

namespace App\Http\Controllers\Concerns;

use App\Models\GroupModuleLecture;
use App\Models\Module;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptQuestion;
use App\Models\ScormResult;
use App\Models\ScormScore;
use Illuminate\Support\Facades\DB;

trait InteractsWithLectureProgressStats
{
    private function buildLectureStats($lectures, int $userId): array
    {
        $lectureIds = $lectures->pluck('id')->all();

        $attempts = QuizAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('lecture_id', $lectureIds)
            ->orderByDesc('finished_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('lecture_id')
            ->map(fn ($rows) => $rows->first());

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

        $scores = ScormScore::query()
            ->where('user_id', $userId)
            ->whereIn('lecture_id', $lectureIds)
            ->get()
            ->keyBy('lecture_id');

        $started = ScormResult::query()
            ->where('user_id', $userId)
            ->whereIn('lecture_id', $lectureIds)
            ->select('lecture_id', DB::raw('COUNT(*) as c'))
            ->groupBy('lecture_id')
            ->pluck('c', 'lecture_id');

        $stats = [];

        foreach ($lectures as $lec) {
            if ((bool) ($lec->quiz_enabled ?? false)) {
                $attempt = $attempts->get($lec->id);
                $planned = (int) ($lec->quiz_questions_per_attempt ?? 0);

                if (! $attempt) {
                    $stats[$lec->id] = [
                        'status' => 'not_started',
                        'quiz' => true,
                        'questions_total' => $planned,
                        'questions_answered' => 0,
                        'questions_correct' => 0,
                        'quiz_score' => null,
                        'quiz_finished' => false,
                        'slides' => (int) ($lec->slide_count ?? 0),
                        'session_time' => null,
                    ];

                    continue;
                }

                $agg = $attemptAgg->get($attempt->id);
                $total = (int) ($agg->total ?? $attempt->total_questions ?? $planned ?? 0);
                $answered = (int) ($agg->answered ?? 0);
                $correct = (int) ($agg->correct ?? 0);
                $score = ! is_null($attempt->percent)
                    ? (int) $attempt->percent
                    : (($total > 0) ? (int) round(($correct / $total) * 100) : null);
                $finished = ! is_null($attempt->finished_at);

                if (! $finished) {
                    $status = $answered > 0 ? 'in_progress' : 'not_started';
                } else {
                    $status = ($score !== null && $score >= 50) ? 'completed' : 'failed';
                }

                $stats[$lec->id] = [
                    'status' => $status,
                    'quiz' => true,
                    'questions_total' => $total,
                    'questions_answered' => $answered,
                    'questions_correct' => $correct,
                    'quiz_score' => $score,
                    'quiz_finished' => $finished,
                    'slides' => (int) ($lec->slide_count ?? 0),
                    'session_time' => null,
                ];

                continue;
            }

            $hasStarted = (int) ($started[$lec->id] ?? 0) > 0;
            $sc = $scores->get($lec->id);
            $lessonStatus = strtolower((string) ($sc->lesson_status ?? ''));
            $isCompleted = in_array($lessonStatus, ['completed', 'passed'], true) || (bool) ($sc->is_completed ?? false);

            if (! $hasStarted) {
                $status = 'not_started';
            } elseif ($isCompleted) {
                $status = 'completed';
            } else {
                $status = 'in_progress';
            }

            $stats[$lec->id] = [
                'status' => $status,
                'quiz' => false,
                'questions_total' => 0,
                'questions_answered' => 0,
                'questions_correct' => 0,
                'quiz_score' => null,
                'quiz_finished' => false,
                'slides' => (int) ($lec->slide_count ?? 0),
                'session_time' => $sc->session_time ?? null,
            ];
        }

        return $stats;
    }

    private function applyGroupLessonOverrides(Module $module, ?int $groupId, bool $filterHidden = true): void
    {
        if (! $groupId) {
            return;
        }

        $over = GroupModuleLecture::query()
            ->where('group_id', $groupId)
            ->where('module_id', $module->id)
            ->get()
            ->keyBy('lecture_id');

        if ($over->isEmpty()) {
            return;
        }

        $module->sections->each(function ($sec) use ($over, $filterHidden) {
            $lectures = $sec->lectures;

            if ($filterHidden) {
                $lectures = $lectures->filter(function ($lec) use ($over) {
                    $row = $over->get($lec->id);

                    return $row ? (bool) $row->is_enabled : true;
                });
            }

            $lectures = $lectures->sortBy(function ($lec) use ($over) {
                $row = $over->get($lec->id);

                return $row ? (int) $row->position : (int) $lec->position;
            })->values();

            $sec->setRelation('lectures', $lectures);
        });
    }
}
