<?php

namespace App\Services;

use App\Models\ModuleLecture;
use App\Models\Progression;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptQuestion;
use App\Models\ScormInteraction;
use App\Models\ScormResult;
use App\Models\ScormScore;
use App\Models\VideoSegmentTracking;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class LearningAnalyticsService
{
    public function collectSnapshots(array $userIds, array $lectureIds): Collection
    {
        $userIds = $this->sanitizeIds($userIds);
        $lectureIds = $this->sanitizeIds($lectureIds);

        if ($userIds === [] || $lectureIds === []) {
            return collect();
        }

        $lectures = ModuleLecture::query()
            ->whereIn('id', $lectureIds)
            ->get([
                'id',
                'module_id',
                'quiz_enabled',
                'quiz_questions_per_attempt',
                'question_count',
            ])
            ->keyBy('id');

        if ($lectures->isEmpty()) {
            return collect();
        }

        $rows = [];

        $ensureRow = function (int $userId, int $lectureId) use (&$rows, $lectures): ?string {
            $lecture = $lectures->get($lectureId);
            if (! $lecture) {
                return null;
            }

            $key = $this->pairKey($userId, $lectureId);

            if (! isset($rows[$key])) {
                $rows[$key] = [
                    'user_id' => $userId,
                    'lecture_id' => $lectureId,
                    'module_id' => (int) $lecture->module_id,
                    'is_quiz' => (bool) ($lecture->quiz_enabled ?? false),
                    'planned_quiz_questions' => (int) ($lecture->quiz_questions_per_attempt ?? 0),
                    'planned_scorm_questions' => (int) ($lecture->question_count ?? 0),
                    'progression_completed_at' => null,
                    'scorm_started_at' => null,
                    'scorm_interaction_at' => null,
                    'scorm_completed' => false,
                    'scorm_score_percent' => null,
                    'scorm_last_activity_at' => null,
                    'video_last_activity_at' => null,
                    'video_watch_time' => 0,
                    'quiz_last_activity_at' => null,
                    'quiz_finished' => false,
                    'quiz_passed' => false,
                    'quiz_score_percent' => null,
                    'quiz_answered' => 0,
                    'quiz_correct' => 0,
                    'quiz_total' => 0,
                ];
            }

            return $key;
        };

        Progression::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('lecture_id', $lectureIds)
            ->selectRaw('user_id, lecture_id, MAX(completed_at) as completed_at')
            ->groupBy('user_id', 'lecture_id')
            ->get()
            ->each(function ($row) use (&$rows, $ensureRow): void {
                $key = $ensureRow((int) $row->user_id, (int) $row->lecture_id);
                if (! $key) {
                    return;
                }

                $rows[$key]['progression_completed_at'] = $this->asCarbon($row->completed_at);
            });

        ScormResult::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('lecture_id', $lectureIds)
            ->selectRaw('user_id, lecture_id, MAX(updated_at) as last_started_at')
            ->groupBy('user_id', 'lecture_id')
            ->get()
            ->each(function ($row) use (&$rows, $ensureRow): void {
                $key = $ensureRow((int) $row->user_id, (int) $row->lecture_id);
                if (! $key) {
                    return;
                }

                $rows[$key]['scorm_started_at'] = $this->asCarbon($row->last_started_at);
            });

        ScormInteraction::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('lecture_id', $lectureIds)
            ->selectRaw('user_id, lecture_id, MAX(updated_at) as last_interaction_at')
            ->groupBy('user_id', 'lecture_id')
            ->get()
            ->each(function ($row) use (&$rows, $ensureRow): void {
                $key = $ensureRow((int) $row->user_id, (int) $row->lecture_id);
                if (! $key) {
                    return;
                }

                $rows[$key]['scorm_interaction_at'] = $this->asCarbon($row->last_interaction_at);
            });

        ScormScore::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('lecture_id', $lectureIds)
            ->get([
                'user_id',
                'lecture_id',
                'last_score',
                'best_score',
                'lesson_status',
                'is_completed',
                'last_attempt_at',
                'updated_at',
            ])
            ->each(function (ScormScore $score) use (&$rows, $ensureRow): void {
                $key = $ensureRow((int) $score->user_id, (int) $score->lecture_id);
                if (! $key) {
                    return;
                }

                $lessonStatus = strtolower((string) ($score->lesson_status ?? ''));
                $rows[$key]['scorm_completed'] = in_array($lessonStatus, ['completed', 'passed'], true)
                    || (bool) ($score->is_completed ?? false);
                $rows[$key]['scorm_score_percent'] = is_null($score->last_score)
                    ? (is_null($score->best_score) ? null : (int) $score->best_score)
                    : (int) $score->last_score;
                $rows[$key]['scorm_last_activity_at'] = $this->maxCarbon(
                    $rows[$key]['scorm_last_activity_at'],
                    $score->last_attempt_at,
                    $score->updated_at,
                );
            });

        VideoSegmentTracking::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('lecture_id', $lectureIds)
            ->selectRaw('user_id, lecture_id, MAX(updated_at) as last_video_at, SUM(total_watch_time) as watch_time')
            ->groupBy('user_id', 'lecture_id')
            ->get()
            ->each(function ($row) use (&$rows, $ensureRow): void {
                $key = $ensureRow((int) $row->user_id, (int) $row->lecture_id);
                if (! $key) {
                    return;
                }

                $rows[$key]['video_last_activity_at'] = $this->asCarbon($row->last_video_at);
                $rows[$key]['video_watch_time'] = (int) ($row->watch_time ?? 0);
            });

        $latestQuizAttempts = QuizAttempt::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('lecture_id', $lectureIds)
            ->get([
                'id',
                'user_id',
                'lecture_id',
                'started_at',
                'finished_at',
                'score',
                'percent',
                'passed',
                'updated_at',
                'created_at',
                'total_questions',
            ])
            ->groupBy(fn (QuizAttempt $attempt) => $this->pairKey((int) $attempt->user_id, (int) $attempt->lecture_id))
            ->map(function (Collection $attempts) {
                return $attempts
                    ->sortByDesc(function (QuizAttempt $attempt) {
                        return sprintf(
                            '%020d-%020d',
                            $this->maxTimestamp(
                            $attempt->finished_at,
                            $attempt->started_at,
                            $attempt->updated_at,
                            $attempt->created_at,
                            ),
                            (int) $attempt->id
                        );
                    })
                    ->first();
            });

        $attemptAgg = collect();
        $attemptIds = $latestQuizAttempts->filter()->pluck('id')->all();
        if ($attemptIds !== []) {
            $attemptAgg = QuizAttemptQuestion::query()
                ->whereIn('attempt_id', $attemptIds)
                ->selectRaw(
                    'attempt_id, COUNT(*) as total, ' .
                    'SUM(CASE WHEN answered_at IS NOT NULL THEN 1 ELSE 0 END) as answered, ' .
                    'SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct'
                )
                ->groupBy('attempt_id')
                ->get()
                ->keyBy('attempt_id');
        }

        $latestQuizAttempts->each(function (?QuizAttempt $attempt, string $pairKey) use (&$rows, $attemptAgg, $ensureRow): void {
            if (! $attempt) {
                return;
            }

            $key = $ensureRow((int) $attempt->user_id, (int) $attempt->lecture_id);
            if (! $key) {
                return;
            }

            $agg = $attemptAgg->get($attempt->id);
            $total = (int) ($agg->total ?? $attempt->total_questions ?? 0);
            $answered = (int) ($agg->answered ?? 0);
            $correct = (int) ($agg->correct ?? 0);

            $rows[$key]['quiz_total'] = $total;
            $rows[$key]['quiz_answered'] = $answered;
            $rows[$key]['quiz_correct'] = $correct;
            $rows[$key]['quiz_finished'] = ! is_null($attempt->finished_at);
            $rows[$key]['quiz_passed'] = (bool) ($attempt->passed ?? false);
            $rows[$key]['quiz_score_percent'] = ! is_null($attempt->percent)
                ? (int) $attempt->percent
                : (! is_null($attempt->score) ? (int) $attempt->score : ($total > 0 ? (int) round(($correct / $total) * 100) : null));
            $rows[$key]['quiz_last_activity_at'] = $this->maxCarbon(
                $rows[$key]['quiz_last_activity_at'],
                $attempt->finished_at,
                $attempt->started_at,
                $attempt->updated_at,
                $attempt->created_at,
            );
        });

        return collect($rows)
            ->map(fn (array $row) => $this->finalizeSnapshot($row))
            ->values();
    }

    public function aggregateUserMetrics(Collection $snapshots, ?CarbonInterface $recentSince = null): Collection
    {
        return $snapshots
            ->groupBy('user_id')
            ->map(function (Collection $items) use ($recentSince) {
                $lastActivityAt = $this->maxCarbon(...$items->pluck('last_activity_at')->all());
                $scoreItems = $items->filter(fn (array $item) => $item['evaluable'] && ! is_null($item['score_percent']));
                $scoreCount = $scoreItems->count();

                return [
                    'started' => $items->contains(fn (array $item) => $item['started']),
                    'completed_count' => $items->where('completed', true)->count(),
                    'evaluated_count' => $items->where('evaluable', true)->count(),
                    'success_count' => $items->where('successful', true)->count(),
                    'success_rate' => $items->where('evaluable', true)->count() > 0
                        ? (int) round(($items->where('successful', true)->count() / $items->where('evaluable', true)->count()) * 100)
                        : 0,
                    'score_sum' => (int) $scoreItems->sum('score_percent'),
                    'score_count' => $scoreCount,
                    'last_activity_at' => $lastActivityAt,
                    'recent' => $recentSince
                        ? ($lastActivityAt instanceof CarbonInterface && $lastActivityAt->greaterThanOrEqualTo($recentSince))
                        : false,
                ];
            });
    }

    public function aggregateScopeMetrics(Collection $snapshots, ?CarbonInterface $recentSince = null): array
    {
        $userMetrics = $this->aggregateUserMetrics($snapshots, $recentSince);

        $startedUsersCount = $userMetrics->filter(fn (array $metric) => $metric['started'])->count();
        $recentUsersCount = $recentSince
            ? $userMetrics->filter(fn (array $metric) => $metric['recent'])->count()
            : 0;

        $evaluatedCount = (int) $userMetrics->sum('evaluated_count');
        $successCount = (int) $userMetrics->sum('success_count');
        $scoreCount = (int) $userMetrics->sum('score_count');
        $scoreSum = (int) $userMetrics->sum('score_sum');

        return [
            'started_users_count' => $startedUsersCount,
            'recent_users_count' => $recentUsersCount,
            'completed_count' => (int) $userMetrics->sum('completed_count'),
            'evaluated_count' => $evaluatedCount,
            'success_count' => $successCount,
            'success_rate' => $evaluatedCount > 0
                ? (int) round(($successCount / $evaluatedCount) * 100)
                : 0,
            'average_score' => $scoreCount > 0
                ? (int) round($scoreSum / $scoreCount)
                : 0,
            'last_activity_at' => $this->maxCarbon(...$userMetrics->pluck('last_activity_at')->all()),
            'user_metrics' => $userMetrics,
        ];
    }

    public function collectActivityEvents(
        array $userIds,
        array $lectureIds,
        CarbonInterface $startAt,
        ?CarbonInterface $endAt = null
    ): Collection {
        $userIds = $this->sanitizeIds($userIds);
        $lectureIds = $this->sanitizeIds($lectureIds);

        if ($userIds === [] || $lectureIds === []) {
            return collect();
        }

        $endAt ??= now();

        $events = collect();

        $events = $events->concat(
            Progression::query()
                ->whereIn('user_id', $userIds)
                ->whereIn('lecture_id', $lectureIds)
                ->whereNotNull('completed_at')
                ->whereBetween('completed_at', [$startAt->toDateTimeString(), $endAt->toDateTimeString()])
                ->get(['user_id', 'lecture_id', 'completed_at'])
                ->map(fn (Progression $row) => [
                    'user_id' => (int) $row->user_id,
                    'lecture_id' => (int) $row->lecture_id,
                    'activity_at' => $this->asCarbon($row->completed_at),
                ])
        );

        $events = $events->concat(
            QuizAttempt::query()
                ->whereIn('user_id', $userIds)
                ->whereIn('lecture_id', $lectureIds)
                ->whereRaw('COALESCE(finished_at, started_at, updated_at, created_at) BETWEEN ? AND ?', [
                    $startAt->toDateTimeString(),
                    $endAt->toDateTimeString(),
                ])
                ->get(['user_id', 'lecture_id', 'started_at', 'finished_at', 'updated_at', 'created_at'])
                ->map(fn (QuizAttempt $row) => [
                    'user_id' => (int) $row->user_id,
                    'lecture_id' => (int) $row->lecture_id,
                    'activity_at' => $this->maxCarbon(
                        $row->finished_at,
                        $row->started_at,
                        $row->updated_at,
                        $row->created_at,
                    ),
                ])
        );

        $events = $events->concat(
            VideoSegmentTracking::query()
                ->whereIn('user_id', $userIds)
                ->whereIn('lecture_id', $lectureIds)
                ->whereBetween('updated_at', [$startAt->toDateTimeString(), $endAt->toDateTimeString()])
                ->get(['user_id', 'lecture_id', 'updated_at'])
                ->map(fn (VideoSegmentTracking $row) => [
                    'user_id' => (int) $row->user_id,
                    'lecture_id' => (int) $row->lecture_id,
                    'activity_at' => $this->asCarbon($row->updated_at),
                ])
        );

        $events = $events->concat(
            ScormInteraction::query()
                ->whereIn('user_id', $userIds)
                ->whereIn('lecture_id', $lectureIds)
                ->whereBetween('updated_at', [$startAt->toDateTimeString(), $endAt->toDateTimeString()])
                ->get(['user_id', 'lecture_id', 'updated_at'])
                ->map(fn (ScormInteraction $row) => [
                    'user_id' => (int) $row->user_id,
                    'lecture_id' => (int) $row->lecture_id,
                    'activity_at' => $this->asCarbon($row->updated_at),
                ])
        );

        return $events
            ->filter(fn (array $event) => $event['activity_at'] instanceof CarbonInterface)
            ->values();
    }

    private function finalizeSnapshot(array $row): array
    {
        $scorePercent = null;
        $completed = false;
        $evaluable = false;
        $successful = false;
        $started = false;

        if ($row['is_quiz']) {
            $started = $row['quiz_last_activity_at'] instanceof CarbonInterface
                || $row['quiz_answered'] > 0;
            $completed = $row['quiz_finished'] && $row['quiz_passed'];
            $scorePercent = is_null($row['quiz_score_percent']) ? null : (int) $row['quiz_score_percent'];
            $evaluable = $row['quiz_finished'];
            $successful = $evaluable && $row['quiz_passed'];
        } else {
            $started = $row['progression_completed_at'] instanceof CarbonInterface
                || $row['scorm_started_at'] instanceof CarbonInterface
                || $row['scorm_interaction_at'] instanceof CarbonInterface
                || $row['scorm_last_activity_at'] instanceof CarbonInterface
                || $row['video_last_activity_at'] instanceof CarbonInterface;
            $completed = $row['progression_completed_at'] instanceof CarbonInterface
                || $row['scorm_completed'];
            $scorePercent = is_null($row['scorm_score_percent']) ? null : (int) $row['scorm_score_percent'];
            $evaluable = $completed && ! is_null($scorePercent);
            $successful = $evaluable && $scorePercent >= 50;
        }

        return [
            'user_id' => (int) $row['user_id'],
            'lecture_id' => (int) $row['lecture_id'],
            'module_id' => (int) $row['module_id'],
            'started' => $started,
            'completed' => $completed,
            'evaluable' => $evaluable,
            'successful' => $successful,
            'score_percent' => $scorePercent,
            'last_activity_at' => $this->maxCarbon(
                $row['progression_completed_at'],
                $row['scorm_started_at'],
                $row['scorm_interaction_at'],
                $row['scorm_last_activity_at'],
                $row['video_last_activity_at'],
                $row['quiz_last_activity_at'],
            ),
        ];
    }

    private function sanitizeIds(array $ids): array
    {
        return collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function pairKey(int $userId, int $lectureId): string
    {
        return $userId . ':' . $lectureId;
    }

    private function asCarbon(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy();
        }

        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value);
        }

        if (empty($value)) {
            return null;
        }

        return Carbon::parse((string) $value);
    }

    private function maxCarbon(mixed ...$values): ?Carbon
    {
        $dates = collect($values)
            ->map(fn ($value) => $this->asCarbon($value))
            ->filter();

        if ($dates->isEmpty()) {
            return null;
        }

        return $dates
            ->sortByDesc(fn (Carbon $date) => $date->timestamp)
            ->first()
            ?->copy();
    }

    private function maxTimestamp(mixed ...$values): int
    {
        return $this->maxCarbon(...$values)?->timestamp ?? 0;
    }
}
