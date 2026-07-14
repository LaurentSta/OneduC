<?php

namespace App\Services;

use App\Data\ParcoursFormateur;
use App\Models\TrainerPathActivityAttempt;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TrainerPathQualityDashboardService
{
    private const MODULE_KEY = 'organiser-ses-parcours';

    /**
     * @return array<string, mixed>
     */
    public function moduleTwoDashboard(): array
    {
        $requirements = ParcoursFormateur::moduleCompletionRequirements(self::MODULE_KEY);
        $activities = $this->moduleActivities($requirements);
        $trainers = DB::table('users')
            ->where('role', 'formateur')
            ->orderBy('name')
            ->orderBy('prenom')
            ->get(['id', 'prenom', 'name', 'username', 'email']);

        $attempts = TrainerPathActivityAttempt::query()
            ->where('module_key', self::MODULE_KEY)
            ->orderBy('submitted_at')
            ->get();

        $questionnaires = DB::table('trainer_module_questionnaire_submissions')
            ->where('module_key', self::MODULE_KEY)
            ->orderByDesc('submitted_at')
            ->get(['user_id', 'submitted_at', 'responses'])
            ->groupBy('user_id');

        $trainerRows = $this->trainerRows($trainers, $attempts, $requirements, $questionnaires);
        $completedRows = $trainerRows->filter(fn (array $row): bool => $row['is_completed']);
        $startedRows = $trainerRows->filter(fn (array $row): bool => $row['started']);
        $transfer = $this->transferIndicator($completedRows);
        $trainerRows = $trainerRows->map(function (array $row) use ($transfer): array {
            $transferRow = $transfer['by_trainer'][$row['id']] ?? null;

            return [
                ...$row,
                'real_group_created' => $transferRow !== null,
                'real_group_created_at' => $transferRow['created_at'] ?? null,
                'real_group_name' => $transferRow['name'] ?? null,
            ];
        });

        $activityStats = $this->activityStats($activities, $attempts);
        $deep = $this->deepScores();
        $totalFirstAttempts = $activityStats->sum('first_attempts');
        $totalFirstSuccesses = $activityStats->sum('first_successes');
        $firstAttemptAlert = $activityStats->contains(
            fn (array $activity): bool => $activity['first_attempts'] > 0
                && $activity['first_success_rate'] !== null
                && $activity['first_success_rate'] < 60
        );
        $deepAlert = collect($deep['dimensions'])->contains(
            fn (array $dimension): bool => $dimension['average'] !== null && $dimension['average'] < 4
        );

        return [
            'module' => [
                'key' => self::MODULE_KEY,
                'title' => ParcoursFormateur::rawModules()[self::MODULE_KEY]['title'] ?? 'Module 2',
                'required_count' => count($requirements),
            ],
            'completion' => [
                'started_count' => $startedRows->count(),
                'completed_count' => $completedRows->count(),
                'rate' => $this->percentage($completedRows->count(), $startedRows->count()),
                'is_low_sample' => $startedRows->count() < 5,
                'is_alert' => $startedRows->count() >= 5
                    && $this->percentage($completedRows->count(), $startedRows->count()) !== null
                    && $this->percentage($completedRows->count(), $startedRows->count()) < 70,
                'threshold' => '70 %',
            ],
            'first_attempt' => [
                'total_first_attempts' => $totalFirstAttempts,
                'total_first_successes' => $totalFirstSuccesses,
                'rate' => $this->percentage($totalFirstSuccesses, $totalFirstAttempts),
                'low_activity_count' => $activityStats->filter(
                    fn (array $activity): bool => $activity['first_attempts'] > 0
                        && $activity['first_success_rate'] !== null
                        && $activity['first_success_rate'] < 60
                )->count(),
                'is_low_sample' => $totalFirstAttempts < 5,
                'is_alert' => $totalFirstAttempts >= 5 && $firstAttemptAlert,
                'threshold' => '60 % par activité',
            ],
            'deep' => [
                ...$deep,
                'is_low_sample' => $deep['respondent_count'] < 5,
                'is_alert' => $deep['respondent_count'] >= 5 && $deepAlert,
                'threshold' => '4/5 par dimension',
            ],
            'transfer' => [
                ...$transfer,
                'is_low_sample' => $completedRows->count() < 5,
                'is_alert' => $completedRows->count() >= 5 && $transfer['created_count'] < 1,
                'threshold' => 'Au moins 1 groupe réel',
            ],
            'trainers' => $trainerRows->values(),
            'activities' => $activityStats->values(),
        ];
    }

    /**
     * @param  array<string, string>  $requirements
     * @return Collection<int, array<string, mixed>>
     */
    private function moduleActivities(array $requirements): Collection
    {
        $module = ParcoursFormateur::rawModules()[self::MODULE_KEY] ?? [];
        $activities = collect();

        foreach (($module['chapters'] ?? []) as $chapterKey => $chapter) {
            foreach (($chapter['lessons'] ?? []) as $lessonKey => $lesson) {
                $activity = $lesson['activity_page'] ?? null;

                if (is_array($activity) && ! empty($activity['key'])) {
                    $statusKey = ParcoursFormateur::activityStatusKey((string) $chapterKey, (string) $lessonKey, (string) $activity['key']);
                    if (array_key_exists($statusKey, $requirements)) {
                        $activities->push([
                            'status_key' => $statusKey,
                            'chapter_key' => (string) $chapterKey,
                            'lesson_key' => (string) $lessonKey,
                            'activity_key' => (string) $activity['key'],
                            'activity_type' => (string) ($activity['type'] ?? 'sorting'),
                            'label' => (string) ($activity['title'] ?? $lesson['title'] ?? $activity['key']),
                            'lesson_label' => trim((string) ($lesson['code'] ?? '').' '.(string) ($lesson['title'] ?? '')),
                            'is_history_reliable' => true,
                        ]);
                    }
                }

                $completionActivityKey = $lesson['completion_activity_key'] ?? null;
                if (is_string($completionActivityKey) && $completionActivityKey !== '') {
                    $statusKey = ParcoursFormateur::activityStatusKey((string) $chapterKey, (string) $lessonKey, $completionActivityKey);
                    if (array_key_exists($statusKey, $requirements)) {
                        $activities->push([
                            'status_key' => $statusKey,
                            'chapter_key' => (string) $chapterKey,
                            'lesson_key' => (string) $lessonKey,
                            'activity_key' => $completionActivityKey,
                            'activity_type' => (string) ($lesson['completion_activity_type'] ?? 'guided_group_creation'),
                            'label' => (string) ($lesson['title'] ?? $completionActivityKey),
                            'lesson_label' => trim((string) ($lesson['code'] ?? '').' '.(string) ($lesson['title'] ?? '')),
                            'is_history_reliable' => false,
                        ]);
                    }
                }
            }
        }

        return $activities;
    }

    /**
     * @param  Collection<int, object>  $trainers
     * @param  Collection<int, object>  $attempts
     * @param  array<string, string>  $requirements
     * @param  Collection<int|string, Collection<int, object>>  $questionnaires
     * @return Collection<int, array<string, mixed>>
     */
    private function trainerRows(Collection $trainers, Collection $attempts, array $requirements, Collection $questionnaires): Collection
    {
        $attemptsByTrainer = $attempts->groupBy('user_id');

        return $trainers->map(function ($trainer) use ($attemptsByTrainer, $requirements, $questionnaires): array {
            $trainerAttempts = $attemptsByTrainer->get($trainer->id, collect());
            $completedAttempts = $trainerAttempts
                ->where('is_success', true)
                ->filter(function ($attempt) use ($requirements): bool {
                    $statusKey = ParcoursFormateur::activityStatusKey(
                        (string) $attempt->chapter_key,
                        (string) $attempt->lesson_key,
                        (string) $attempt->activity_key
                    );

                    return ($requirements[$statusKey] ?? null) === (string) $attempt->activity_type;
                });
            $completedKeys = $completedAttempts
                ->mapWithKeys(fn ($attempt): array => [
                    ParcoursFormateur::activityStatusKey(
                        (string) $attempt->chapter_key,
                        (string) $attempt->lesson_key,
                        (string) $attempt->activity_key
                    ) => true,
                ])
                ->all();
            $isCompleted = ! empty($requirements) && empty(array_diff_key($requirements, $completedKeys));
            $completionDate = null;

            if ($isCompleted) {
                $completionDate = $completedAttempts
                    ->filter(function ($attempt) use ($requirements): bool {
                        $statusKey = ParcoursFormateur::activityStatusKey(
                            (string) $attempt->chapter_key,
                            (string) $attempt->lesson_key,
                            (string) $attempt->activity_key
                        );

                        return array_key_exists($statusKey, $requirements);
                    })
                    ->max('submitted_at');
            }

            $latestQuestionnaire = $questionnaires->get($trainer->id, collect())->first();

            return [
                'id' => (int) $trainer->id,
                'name' => $this->trainerName($trainer),
                'email' => (string) ($trainer->email ?? ''),
                'started' => $trainerAttempts->isNotEmpty(),
                'completed_steps' => count(array_intersect_key($requirements, $completedKeys)),
                'required_steps' => count($requirements),
                'is_completed' => $isCompleted,
                'completed_at' => $completionDate,
                'questionnaire_received' => $latestQuestionnaire !== null,
                'questionnaire_submitted_at' => $latestQuestionnaire->submitted_at ?? null,
            ];
        });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $activities
     * @param  Collection<int, object>  $attempts
     * @return Collection<int, array<string, mixed>>
     */
    private function activityStats(Collection $activities, Collection $attempts): Collection
    {
        $hasAttemptNumber = Schema::hasColumn('trainer_path_activity_attempts', 'attempt_number');

        return $activities->map(function (array $activity) use ($attempts, $hasAttemptNumber): array {
            $activityAttempts = $attempts
                ->where('chapter_key', $activity['chapter_key'])
                ->where('lesson_key', $activity['lesson_key'])
                ->where('activity_key', $activity['activity_key'])
                ->where('activity_type', $activity['activity_type']);
            $firstAttempts = $hasAttemptNumber
                ? $activityAttempts->where('attempt_number', 1)
                : collect();
            $firstSuccesses = $firstAttempts->where('is_success', true)->count();

            return [
                ...$activity,
                'attempts' => $activityAttempts->count(),
                'first_attempts' => $firstAttempts->count(),
                'first_successes' => $firstSuccesses,
                'first_success_rate' => $this->percentage($firstSuccesses, $firstAttempts->count()),
                'is_history_reliable' => (bool) $activity['is_history_reliable'] && $hasAttemptNumber,
                'reliability_label' => (bool) $activity['is_history_reliable'] && $hasAttemptNumber
                    ? 'Fiable'
                    : 'Historique partiel',
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function deepScores(): array
    {
        $questionnaire = ParcoursFormateur::moduleUsabilityQuestionnaire(self::MODULE_KEY);
        $dimensions = collect($questionnaire['dimensions'] ?? []);
        $submissions = DB::table('trainer_module_questionnaire_submissions')
            ->where('module_key', self::MODULE_KEY)
            ->get(['responses']);

        $scoresByDimension = $dimensions->mapWithKeys(
            fn (array $dimension): array => [$dimension['id'] => [
                'id' => (string) $dimension['id'],
                'title' => (string) $dimension['title'],
                'scores' => [],
                'respondents' => [],
            ]]
        )->all();

        foreach ($submissions as $index => $submission) {
            $responses = $this->decodeJson($submission->responses ?? null);
            $closedItems = collect($responses['closed_items'] ?? []);

            foreach ($dimensions as $dimension) {
                $dimensionScores = [];

                foreach (($dimension['items'] ?? []) as $item) {
                    $answer = $closedItems->firstWhere('item_number', (int) $item['number']);
                    $value = $answer['value'] ?? null;

                    if (! is_numeric($value)) {
                        continue;
                    }

                    $score = (float) $value;
                    if (! empty($item['reversed'])) {
                        $score = 6 - $score;
                    }

                    $dimensionScores[] = $score;
                }

                if ($dimensionScores === []) {
                    continue;
                }

                $dimensionId = (string) $dimension['id'];
                $scoresByDimension[$dimensionId]['scores'][] = array_sum($dimensionScores) / count($dimensionScores);
                $scoresByDimension[$dimensionId]['respondents'][$index] = true;
            }
        }

        $dimensionRows = collect($scoresByDimension)->map(function (array $dimension): array {
            $scores = $dimension['scores'];

            return [
                'id' => $dimension['id'],
                'title' => $dimension['title'],
                'average' => $scores === [] ? null : round(array_sum($scores) / count($scores), 2),
                'respondent_count' => count($dimension['respondents']),
            ];
        })->values();
        $dimensionAverages = $dimensionRows
            ->pluck('average')
            ->filter(fn ($score): bool => $score !== null)
            ->values();

        return [
            'respondent_count' => $submissions->count(),
            'dimensions' => $dimensionRows->all(),
            'global_average' => $dimensionAverages->isEmpty()
                ? null
                : round($dimensionAverages->sum() / $dimensionAverages->count(), 2),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $completedRows
     * @return array<string, mixed>
     */
    private function transferIndicator(Collection $completedRows): array
    {
        $byTrainer = [];
        $hasSandboxColumn = Schema::hasColumn('groups', 'is_sandbox');
        $groups = DB::table('groups')
            ->whereIn('instructor_id', $completedRows->pluck('id'))
            ->whereNull('deleted_at')
            ->when($hasSandboxColumn, fn ($query) => $query->where('is_sandbox', false))
            ->when(! $hasSandboxColumn, fn ($query) => $query->whereRaw('1 = 0'))
            ->orderBy('created_at')
            ->get(['id', 'name', 'instructor_id', 'created_at'])
            ->groupBy('instructor_id');

        foreach ($completedRows as $row) {
            if (empty($row['completed_at'])) {
                continue;
            }

            $completedAt = Carbon::parse($row['completed_at']);
            $deadline = $completedAt->copy()->addDays(30);
            $group = $groups->get($row['id'], collect())->first(function ($group) use ($completedAt, $deadline): bool {
                $createdAt = Carbon::parse($group->created_at);

                return $createdAt->betweenIncluded($completedAt, $deadline);
            });

            if ($group) {
                $byTrainer[$row['id']] = [
                    'id' => (int) $group->id,
                    'name' => (string) $group->name,
                    'created_at' => $group->created_at,
                ];
            }
        }

        return [
            'eligible_count' => $completedRows->count(),
            'created_count' => count($byTrainer),
            'rate' => $this->percentage(count($byTrainer), $completedRows->count()),
            'by_trainer' => $byTrainer,
        ];
    }

    private function percentage(int $numerator, int $denominator): ?float
    {
        if ($denominator === 0) {
            return null;
        }

        return round(($numerator / $denominator) * 100, 1);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function trainerName(object $trainer): string
    {
        $name = trim((string) ($trainer->prenom ?? '').' '.(string) ($trainer->name ?? ''));

        if ($name !== '') {
            return $name;
        }

        return (string) ($trainer->username ?: $trainer->email ?: 'Formateur #'.$trainer->id);
    }
}
