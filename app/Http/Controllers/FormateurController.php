<?php

namespace App\Http\Controllers;

// /home/laurents/Oneduc_Dev/app/Http/Controllers/FormateurController.php

use Carbon\Carbon;
use App\Models\Group;
use App\Models\Module;
use App\Models\User;
use App\Services\LearningAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\ModuleLecture;


class FormateurController extends Controller
{
    public function __construct(
        private readonly LearningAnalyticsService $learningAnalytics,
    ) {
    }

    private function accessibleTrainerGroupIds(int $formateurId): Collection
    {
        return Group::query()
            ->accessibleByTrainer($formateurId)
            ->pluck('groups.id')
            ->map(fn ($groupId) => (int) $groupId)
            ->values();
    }

    /* -------------------------------------------------------------------------
     | Tableau de bord Formateur
     |-------------------------------------------------------------------------- */
    public function FormateurDashboard()
    {
        $formateurId = auth()->id();
        $fifteenDaysAgo = now()->subDays(15);
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);

        $groupCount = $accessibleGroupIds->count();

        $modulesUsed = Module::query()
            ->whereHas('groups', function ($q) use ($formateurId) {
                $q->accessibleByTrainer($formateurId);
            })
            ->distinct('modules.id')
            ->count('modules.id');

        $learnerIds = User::query()
            ->where('role', 'stagiaire')
            ->whereHas('groupesStagiaire', function ($q) use ($accessibleGroupIds) {
                $q->whereIn('groups.id', $accessibleGroupIds->all());
            })
            ->distinct()
            ->pluck('users.id')
            ->values();

        $learnerCount = $learnerIds->count();

        $groupesDashboard = Group::query()
            ->whereIn('id', $accessibleGroupIds->all())
            ->withCount([
                'students as stagiaires_count' => function ($q) {
                    $q->where('role', 'stagiaire');
                },
                'modules as modules_count',
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'created_at']);

        $groupIds = $groupesDashboard->pluck('id')->all();

        $learnerIdsByGroup = $this->resolveGroupLearnerIds($groupIds);
        $lectureIdsByGroup = $this->resolveGroupLectureIds($groupIds);
        $allLectureIds = $lectureIdsByGroup->flatten()->unique()->values()->all();

        $allSnapshots    = $this->learningAnalytics->collectSnapshots($learnerIds->all(), $allLectureIds);
        $overallSnapshots = $this->filterSnapshots($allSnapshots, $learnerIds->all(), $allLectureIds);
        $overallMetrics  = $this->learningAnalytics->aggregateScopeMetrics($overallSnapshots, $fifteenDaysAgo);

        $avgSuccessRate = (int) ($overallMetrics['success_rate'] ?? 0);
        $avgCompletion  = $avgSuccessRate;

        $startedLearnersCount  = (int) ($overallMetrics['started_users_count'] ?? 0);
        $recentLearnersCount   = (int) ($overallMetrics['recent_users_count'] ?? 0);

        $notStartedLearnersCount = max(0, $learnerCount - $startedLearnersCount);
        $inactiveLearnersCount   = max(0, $startedLearnersCount - $recentLearnersCount);

        $groupesDashboard = $groupesDashboard->map(function ($group) use ($allSnapshots, $fifteenDaysAgo, $lectureIdsByGroup, $learnerIdsByGroup) {
            $groupLearnerIds = collect($learnerIdsByGroup->get($group->id, collect()))->values();
            $groupLectureIds = collect($lectureIdsByGroup->get($group->id, collect()))->values()->all();
            $scopeSnapshots  = $this->filterSnapshots($allSnapshots, $groupLearnerIds->all(), $groupLectureIds);
            $scopeMetrics    = $this->learningAnalytics->aggregateScopeMetrics($scopeSnapshots, $fifteenDaysAgo);

            $startedCount = (int) ($scopeMetrics['started_users_count'] ?? 0);
            $recentCount  = (int) ($scopeMetrics['recent_users_count'] ?? 0);

            $group->last_completed_at       = $scopeMetrics['last_activity_at'] ?? null;
            $group->lecons_terminees_count  = (int) ($scopeMetrics['completed_count'] ?? 0);
            $group->taux_reussite           = (int) ($scopeMetrics['success_rate'] ?? 0);
            $group->not_started_count       = max(0, $groupLearnerIds->count() - $startedCount);
            $group->inactive_count          = max(0, $startedCount - $recentCount);
            $group->alert_count             = $group->not_started_count + $group->inactive_count;

            return $group;
        });

        $groupsNeedingAttentionCount = $groupesDashboard
            ->filter(fn ($group) => (int) ($group->alert_count ?? 0) > 0)
            ->count();

        $priorityGroups = $groupesDashboard
            ->sort(function ($a, $b) {
                $alertsA = (int) ($a->alert_count ?? 0);
                $alertsB = (int) ($b->alert_count ?? 0);

                return ($alertsB <=> $alertsA)
                    ?: ((int) ($a->taux_reussite ?? 0) <=> (int) ($b->taux_reussite ?? 0))
                    ?: strcmp((string) $a->name, (string) $b->name);
            })
            ->take(3)
            ->values();

        $moduleInsights = Module::query()
            ->whereHas('groups', function ($q) use ($formateurId) {
                $q->accessibleByTrainer($formateurId);
            })
            ->withCount('lectures')
            ->orderBy('module_title')
            ->get(['id', 'module_title']);

        $lectureIdsByModule = $this->resolveModuleLectureIds($moduleInsights->pluck('id')->all());

        $moduleInsights = $moduleInsights->map(function ($module) use ($accessibleGroupIds, $allSnapshots, $lectureIdsByModule) {
            $stagiaireIds = DB::table('group_module')
                ->join('group_user', 'group_user.group_id', '=', 'group_module.group_id')
                ->join('groups', 'groups.id', '=', 'group_module.group_id')
                ->join('users', 'users.id', '=', 'group_user.user_id')
                ->where('group_module.module_id', $module->id)
                ->whereIn('groups.id', $accessibleGroupIds->all())
                ->where('group_user.role_in_group', 'stagiaire')
                ->where('users.role', 'stagiaire')
                ->pluck('users.id')
                ->unique()
                ->values();

            $moduleLectureIds = collect($lectureIdsByModule->get($module->id, collect()))->values()->all();
            $scopeSnapshots   = $this->filterSnapshots($allSnapshots, $stagiaireIds->all(), $moduleLectureIds);
            $scopeMetrics     = $this->learningAnalytics->aggregateScopeMetrics($scopeSnapshots);

            $module->stagiaires_count = $stagiaireIds->count();
            $module->groupes_count    = (int) DB::table('group_module')
                ->join('groups', 'groups.id', '=', 'group_module.group_id')
                ->where('group_module.module_id', $module->id)
                ->whereIn('groups.id', $accessibleGroupIds->all())
                ->count();
            $module->avg_score    = (int) ($scopeMetrics['average_score'] ?? 0);
            $module->started_count = (int) ($scopeMetrics['started_users_count'] ?? 0);
            $topFailed = collect();

            if ($stagiaireIds->isNotEmpty()) {
                $topFailed = DB::table('quiz_attempt_questions as qaq')
                    ->join('quiz_attempts as qa', 'qa.id', '=', 'qaq.attempt_id')
                    ->join('module_lectures as ml', 'ml.id', '=', 'qa.lecture_id')
                    ->join('module_sections as ms', 'ms.id', '=', 'ml.section_id')
                    ->join('quiz_questions as qq', 'qq.id', '=', 'qaq.question_id')
                    ->where('ms.module_id', $module->id)
                    ->whereIn('qa.user_id', $stagiaireIds)
                    ->select(
                        'qq.question_text',
                        DB::raw('count(*) as total_attempts'),
                        DB::raw('sum(case when qaq.is_correct = 0 then 1 else 0 end) as failures')
                    )
                    ->groupBy('qq.id', 'qq.question_text')
                    ->having('failures', '>', 0)
                    ->orderByDesc('failures')
                    ->take(1)
                    ->get()
                    ->map(function ($question) {
                        $question->fail_rate = $question->total_attempts > 0
                            ? (int) round(($question->failures / $question->total_attempts) * 100)
                            : 0;

                        return $question;
                    });
            }

            $module->start_rate = $module->stagiaires_count > 0
                ? (int) round(($module->started_count / $module->stagiaires_count) * 100)
                : 0;

            $mainDifficulty = $topFailed->first();
            $module->top_failed_question  = $mainDifficulty->question_text ?? null;
            $module->top_failed_rate      = $mainDifficulty->fail_rate ?? 0;
            $module->top_failed_failures  = (int) ($mainDifficulty->failures ?? 0);

            if ($module->start_rate < 50) {
                $module->attention_label  = 'Faible démarrage';
                $module->attention_variant = 'amber';
                $module->attention_detail  = $module->stagiaires_count > 0
                    ? $module->started_count . ' stagiaire(s) sur ' . $module->stagiaires_count . ' ont commencé.'
                    : 'Aucun stagiaire affecté pour le moment.';
            } elseif ($module->avg_score < 50) {
                $module->attention_label  = 'Résultats faibles';
                $module->attention_variant = 'red';
                $module->attention_detail  = 'Le score moyen est en dessous du seuil de réussite.';
            } elseif (!empty($module->top_failed_question)) {
                $module->attention_label  = 'Difficultés quiz';
                $module->attention_variant = 'blue';
                $module->attention_detail  = 'Question la plus ratée : ' . $module->top_failed_rate . '% d échec.';
            } else {
                $module->attention_label  = 'Bon suivi';
                $module->attention_variant = 'green';
                $module->attention_detail  = 'Le module démarre correctement et les résultats sont stables.';
            }

            $module->attention_score = max(0, 70 - $module->start_rate)
                + max(0, 65 - $module->avg_score)
                + min(25, $module->top_failed_failures);

            return $module;
        });

        $modulesNeedingAttentionCount = $moduleInsights
            ->filter(function ($module) {
                return $module->start_rate < 60
                    || $module->avg_score < 60
                    || !empty($module->top_failed_question);
            })
            ->count();

        $priorityModules = $moduleInsights
            ->sort(function ($a, $b) {
                return ((int) ($b->attention_score ?? 0) <=> (int) ($a->attention_score ?? 0))
                    ?: ((int) ($b->stagiaires_count ?? 0) <=> (int) ($a->stagiaires_count ?? 0))
                    ?: strcmp((string) $a->module_title, (string) $b->module_title);
            })
            ->take(3)
            ->values();

        return view('formateur.index', compact(
            'groupCount',
            'modulesUsed',
            'learnerCount',
            'avgSuccessRate',
            'avgCompletion',
            'notStartedLearnersCount',
            'inactiveLearnersCount',
            'groupsNeedingAttentionCount',
            'modulesNeedingAttentionCount',
            'priorityGroups',
            'priorityModules'
        ));
    }

    public function dashboardActivity(Request $request)
    {
        $formateurId = auth()->id();
        $range = $this->sanitizeDashboardActivityRange($request->query('range'));
        $cacheTtl = match ($range) {
            'day'   => now()->addMinutes(2),
            'week'  => now()->addMinutes(5),
            default => now()->addMinutes(10),
        };

        $payload = Cache::remember(
            "formateur-dashboard-activity:{$formateurId}:{$range}",
            $cacheTtl,
            fn () => $this->buildDashboardActivityPayload($formateurId, $range)
        );

        return response()->json($payload);
    }

    private function sanitizeDashboardActivityRange(?string $range): string
    {
        return in_array($range, ['day', 'week', 'month', 'year'], true) ? $range : 'week';
    }

    private function buildDashboardActivityPayload(int $formateurId, string $range): array
    {
        $config = $this->resolveDashboardActivityConfig($range);
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);

        $groups = Group::query()
            ->whereIn('id', $accessibleGroupIds->all())
            ->withCount([
                'students as learners_count' => function ($query) {
                    $query->where('users.role', 'stagiaire');
                },
            ])
            ->orderBy('name')
            ->get(['id', 'name']);

        $groupsWithLearnersCount = $groups->filter(fn ($group) => (int) $group->learners_count > 0)->count();
        $totalLearners = (int) $groups->sum('learners_count');

        if ($groups->isEmpty()) {
            return $this->emptyDashboardPayload($range, $config, 0, 0);
        }

        $groupIds = $groups->pluck('id')->all();
        $learnerIdsByGroup = $this->resolveGroupLearnerIds($groupIds);
        $lectureIdsByGroup = $this->resolveGroupLectureIds($groupIds);

        $allLearnerIds = $learnerIdsByGroup->flatten()->unique()->values()->all();
        $allLectureIds = $lectureIdsByGroup->flatten()->unique()->values()->all();

        if ($allLearnerIds === [] || $allLectureIds === []) {
            return array_merge($this->emptyDashboardPayload($range, $config, $groups->count(), $groupsWithLearnersCount), [
                'summary' => [
                    'groups_count'              => $groups->count(),
                    'groups_with_learners_count'=> $groupsWithLearnersCount,
                    'learners_count'            => $totalLearners,
                    'current_average_rate'      => 0,
                    'peak_average_rate'         => 0,
                    'peak_label'                => null,
                ],
                'meta' => [
                    'chart_truncated' => false,
                    'empty_message'   => $totalLearners === 0
                        ? 'Vos groupes existent déjà, mais aucun stagiaire n y est encore rattaché.'
                        : 'Des stagiaires sont rattachés, mais aucun module ne remonte encore d activite exploitable.',
                ],
            ]);
        }

        $events = $this->learningAnalytics->collectActivityEvents(
            $allLearnerIds,
            $allLectureIds,
            $config['query_start_at'],
            $config['query_end_at'],
        );

        $lectureGroupMap = [];
        foreach ($lectureIdsByGroup as $groupId => $lectureIds) {
            foreach ($lectureIds as $lectureId) {
                $lectureGroupMap[(int) $lectureId][(int) $groupId] = true;
            }
        }

        $userGroupMap = [];
        foreach ($learnerIdsByGroup as $groupId => $learnerIds) {
            foreach ($learnerIds as $learnerId) {
                $userGroupMap[(int) $learnerId][(int) $groupId] = true;
            }
        }

        $bucketMembership = [];
        foreach ($events as $event) {
            $userId    = (int) ($event['user_id'] ?? 0);
            $lectureId = (int) ($event['lecture_id'] ?? 0);
            $activityAt = $event['activity_at'] ?? null;

            if (!$activityAt instanceof Carbon) continue;

            $bucketKey = $activityAt->format($config['bucket_format']);
            if (!in_array($bucketKey, $config['bucket_keys'], true)) continue;

            $matchedGroups = $this->intersectSetKeys(
                $lectureGroupMap[$lectureId] ?? [],
                $userGroupMap[$userId] ?? [],
            );

            foreach ($matchedGroups as $groupId) {
                $bucketMembership[$groupId][$bucketKey][$userId] = true;
            }
        }

        $tableGroups = $groups
            ->map(function ($group) use ($bucketMembership, $config) {
                $bucketMap    = collect($bucketMembership[$group->id] ?? []);
                $learnersCount = (int) $group->learners_count;
                $points = [];

                foreach ($config['bucket_keys'] as $bucketKey) {
                    $activeUsers = is_array($bucketMap[$bucketKey] ?? null)
                        ? count($bucketMap[$bucketKey])
                        : 0;
                    $points[] = $learnersCount > 0
                        ? (int) round(($activeUsers / $learnersCount) * 100)
                        : 0;
                }

                $latestRate  = count($points) > 0 ? (int) end($points) : 0;
                $averageRate = count($points) > 0 ? (int) round(array_sum($points) / count($points)) : 0;
                $firstRate   = $points[0] ?? 0;

                return [
                    'id'            => (int) $group->id,
                    'name'          => (string) $group->name,
                    'learners_count'=> $learnersCount,
                    'points'        => $points,
                    'latest_rate'   => $latestRate,
                    'average_rate'  => $averageRate,
                    'trend'         => $latestRate - $firstRate,
                ];
            })
            ->sort(function (array $left, array $right) {
                return ($right['latest_rate'] <=> $left['latest_rate'])
                    ?: ($right['average_rate'] <=> $left['average_rate'])
                    ?: ($right['learners_count'] <=> $left['learners_count'])
                    ?: strcmp($left['name'], $right['name']);
            })
            ->values();

        $palette = ['#F97316', '#004461', '#10B981', '#EF4444', '#8B5CF6', '#0EA5E9'];

        $chartGroups = $tableGroups
            ->take(6)
            ->values()
            ->map(function (array $group, int $index) use ($palette) {
                $group['color'] = $palette[$index % count($palette)];
                return $group;
            })
            ->all();

        $averagePoints = [];
        foreach ($config['bucket_keys'] as $index => $bucketKey) {
            $averagePoints[] = $tableGroups->isNotEmpty()
                ? (int) round($tableGroups->avg(fn (array $group) => $group['points'][$index] ?? 0))
                : 0;
        }

        $peakAverageRate = count($averagePoints) > 0 ? max($averagePoints) : 0;
        $peakIndex = count($averagePoints) > 0 ? array_search($peakAverageRate, $averagePoints, true) : false;

        return [
            'range'                  => $range,
            'title'                  => $config['title'],
            'subtitle'               => $config['subtitle'],
            'labels'                 => $config['labels'],
            'full_labels'            => $config['full_labels'],
            'visible_label_indexes'  => $config['visible_label_indexes'],
            'average_points'         => $averagePoints,
            'chart_groups'           => $chartGroups,
            'table_groups'           => $tableGroups->all(),
            'summary' => [
                'groups_count'              => $groups->count(),
                'groups_with_learners_count'=> $groupsWithLearnersCount,
                'learners_count'            => $totalLearners,
                'current_average_rate'      => count($averagePoints) > 0 ? (int) end($averagePoints) : 0,
                'peak_average_rate'         => $peakAverageRate,
                'peak_label'                => $peakIndex !== false ? ($config['full_labels'][$peakIndex] ?? null) : null,
            ],
            'meta' => [
                'chart_truncated' => $tableGroups->count() > count($chartGroups),
                'empty_message'   => $totalLearners === 0
                    ? 'Vos groupes existent déjà, mais aucun stagiaire n y est encore rattaché.'
                    : null,
            ],
        ];
    }

    private function emptyDashboardPayload(string $range, array $config, int $groupsCount, int $groupsWithLearnersCount): array
    {
        return [
            'range'                 => $range,
            'title'                 => $config['title'],
            'subtitle'              => $config['subtitle'],
            'labels'                => $config['labels'],
            'full_labels'           => $config['full_labels'],
            'visible_label_indexes' => $config['visible_label_indexes'],
            'average_points'        => array_fill(0, count($config['bucket_keys']), 0),
            'chart_groups'          => [],
            'table_groups'          => [],
            'summary' => [
                'groups_count'              => $groupsCount,
                'groups_with_learners_count'=> $groupsWithLearnersCount,
                'learners_count'            => 0,
                'current_average_rate'      => 0,
                'peak_average_rate'         => 0,
                'peak_label'                => null,
            ],
            'meta' => [
                'chart_truncated' => false,
                'empty_message'   => 'Aucun groupe n est encore rattaché à votre espace formateur.',
            ],
        ];
    }

    private function resolveDashboardActivityConfig(string $range): array
    {
        $now = now();

        if ($range === 'day') {
            $startAt = $now->copy()->subHours(23)->startOfHour();
            $endAt   = $now->copy()->endOfHour();
            $step    = 'hour';
            $format  = 'Y-m-d H:00:00';
            $title   = 'Activité des groupes';
            $subtitle = 'Lecture heure par heure sur les dernières 24 heures.';
            $visibleLabelIndexes = [0, 3, 6, 9, 12, 15, 18, 21, 23];
        } elseif ($range === 'month') {
            $startAt = $now->copy()->subDays(29)->startOfDay();
            $endAt   = $now->copy()->endOfDay();
            $step    = 'day';
            $format  = 'Y-m-d';
            $title   = 'Activité des groupes';
            $subtitle = 'Tendance quotidienne sur les 30 derniers jours.';
            $visibleLabelIndexes = [0, 5, 10, 15, 20, 25, 29];
        } elseif ($range === 'year') {
            $startAt = $now->copy()->subMonths(11)->startOfMonth();
            $endAt   = $now->copy()->endOfMonth();
            $step    = 'month';
            $format  = 'Y-m-01';
            $title   = 'Activité des groupes';
            $subtitle = 'Vision mensuelle sur les 12 derniers mois.';
            $visibleLabelIndexes = range(0, 11);
        } else {
            $startAt = $now->copy()->subDays(6)->startOfDay();
            $endAt   = $now->copy()->endOfDay();
            $step    = 'day';
            $format  = 'Y-m-d';
            $title   = 'Activité des groupes';
            $subtitle = 'Suivi jour par jour sur les 7 derniers jours.';
            $visibleLabelIndexes = range(0, 6);
        }

        $bucketKeys = [];
        $labels     = [];
        $fullLabels = [];
        $cursor     = $startAt->copy();

        while ($cursor <= $endAt) {
            $bucketKeys[] = $cursor->format($format);
            $labels[]     = $this->formatDashboardActivityShortLabel($cursor, $range);
            $fullLabels[] = $this->formatDashboardActivityFullLabel($cursor, $range);

            if ($step === 'month') {
                $cursor->addMonth();
            } elseif ($step === 'hour') {
                $cursor->addHour();
            } else {
                $cursor->addDay();
            }
        }

        return [
            'title'                 => $title,
            'subtitle'              => $subtitle,
            'query_start_at'        => $startAt,
            'query_end_at'          => $now,
            'bucket_format'         => $format,
            'bucket_keys'           => $bucketKeys,
            'labels'                => $labels,
            'full_labels'           => $fullLabels,
            'visible_label_indexes' => $visibleLabelIndexes,
        ];
    }

    private function formatDashboardActivityShortLabel(Carbon $date, string $range): string
    {
        return match ($range) {
            'day'  => $date->translatedFormat('H\h'),
            'year' => ucfirst($date->locale('fr')->translatedFormat('M')),
            default => $date->translatedFormat('d/m'),
        };
    }

    private function formatDashboardActivityFullLabel(Carbon $date, string $range): string
    {
        return match ($range) {
            'day'  => $date->translatedFormat('d/m H\h'),
            'year' => ucfirst($date->locale('fr')->translatedFormat('F Y')),
            default => ucfirst($date->locale('fr')->translatedFormat('D d M')),
        };
    }

    private function resolveGroupLearnerIds(array $groupIds): Collection
    {
        $groupIds = collect($groupIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();

        if ($groupIds === []) return collect();

        $learnerIdsByGroup = DB::table('group_user')
            ->join('users', 'users.id', '=', 'group_user.user_id')
            ->whereIn('group_user.group_id', $groupIds)
            ->where('group_user.role_in_group', 'stagiaire')
            ->where('users.role', 'stagiaire')
            ->select('group_user.group_id', 'group_user.user_id')
            ->get()
            ->groupBy('group_id')
            ->map(fn ($rows) => $rows->pluck('user_id')->map(fn ($id) => (int) $id)->unique()->values());

        return collect($groupIds)
            ->mapWithKeys(fn (int $groupId) => [$groupId => $learnerIdsByGroup->get($groupId, collect())]);
    }

    private function resolveGroupLectureIds(array $groupIds): Collection
    {
        $groupIds = collect($groupIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();

        if ($groupIds === []) return collect();

        $moduleIdsByGroup = DB::table('group_module')
            ->whereIn('group_id', $groupIds)
            ->select('group_id', 'module_id')
            ->get()
            ->groupBy('group_id')
            ->map(fn ($rows) => $rows->pluck('module_id')->map(fn ($id) => (int) $id)->unique()->values());

        $lectureIdsByModule = $this->resolveModuleLectureIds(
            $moduleIdsByGroup->flatten()->unique()->values()->all()
        );

        return collect($groupIds)->mapWithKeys(function (int $groupId) use ($lectureIdsByModule, $moduleIdsByGroup) {
            $lectureIds = collect($moduleIdsByGroup->get($groupId, collect()))
                ->flatMap(fn ($moduleId) => $lectureIdsByModule->get((int) $moduleId, collect()))
                ->unique()
                ->values();

            return [$groupId => $lectureIds];
        });
    }

    private function resolveModuleLectureIds(array $moduleIds): Collection
    {
        $moduleIds = collect($moduleIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();

        if ($moduleIds === []) return collect();

        $lectureIdsByModule = ModuleLecture::query()
            ->whereIn('module_id', $moduleIds)
            ->get(['id', 'module_id'])
            ->groupBy('module_id')
            ->map(fn ($rows) => $rows->pluck('id')->map(fn ($id) => (int) $id)->unique()->values());

        return collect($moduleIds)
            ->mapWithKeys(fn (int $moduleId) => [$moduleId => $lectureIdsByModule->get($moduleId, collect())]);
    }

    private function filterSnapshots(Collection $snapshots, array $userIds, array $lectureIds): Collection
    {
        $userLookup = array_fill_keys(
            collect($userIds)->map(fn ($id) => (int) $id)->filter(fn (int $id) => $id > 0)->values()->all(),
            true
        );

        $lectureLookup = array_fill_keys(
            collect($lectureIds)->map(fn ($id) => (int) $id)->filter(fn (int $id) => $id > 0)->values()->all(),
            true
        );

        if ($userLookup === [] || $lectureLookup === []) return collect();

        return $snapshots->filter(function (array $snapshot) use ($lectureLookup, $userLookup) {
            return isset($userLookup[(int) ($snapshot['user_id'] ?? 0)])
                && isset($lectureLookup[(int) ($snapshot['lecture_id'] ?? 0)]);
        })->values();
    }

    private function intersectSetKeys(array $left, array $right): array
    {
        if ($left === [] || $right === []) return [];

        if (count($left) > count($right)) {
            [$left, $right] = [$right, $left];
        }

        $matches = [];
        foreach (array_keys($left) as $key) {
            if (isset($right[$key])) {
                $matches[] = (int) $key;
            }
        }

        return $matches;
    }
}
