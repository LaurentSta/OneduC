<?php

namespace App\Http\Controllers;

// /home/laurents/Oneduc_Dev/app/Http/Controllers/FormateurController.php

use Carbon\Carbon;
use App\Mail\FormateurWelcome;
use App\Mail\NewFormateurNotification;
use App\Models\Group;
use App\Models\Module;
use App\Models\User;
use App\Services\CodeGeneratorService;
use App\Services\LearningAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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

        // Groupes affichés dans la section "Suivi par groupes" du dashboard
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
        $allLectureIds = $lectureIdsByGroup
            ->flatten()
            ->unique()
            ->values()
            ->all();

        $allSnapshots = $this->learningAnalytics->collectSnapshots($learnerIds->all(), $allLectureIds);
        $overallSnapshots = $this->filterSnapshots($allSnapshots, $learnerIds->all(), $allLectureIds);
        $overallMetrics = $this->learningAnalytics->aggregateScopeMetrics($overallSnapshots, $fifteenDaysAgo);

        $avgSuccessRate = (int) ($overallMetrics['success_rate'] ?? 0);
        $avgCompletion = $avgSuccessRate;

        $startedLearnersCount = (int) ($overallMetrics['started_users_count'] ?? 0);
        $recentLearnersCount = (int) ($overallMetrics['recent_users_count'] ?? 0);

        $notStartedLearnersCount = max(0, $learnerCount - $startedLearnersCount);
        $inactiveLearnersCount = max(0, $startedLearnersCount - $recentLearnersCount);

        $groupesDashboard = $groupesDashboard->map(function ($group) use ($allSnapshots, $fifteenDaysAgo, $lectureIdsByGroup, $learnerIdsByGroup) {
            $groupLearnerIds = collect($learnerIdsByGroup->get($group->id, collect()))->values();
            $groupLectureIds = collect($lectureIdsByGroup->get($group->id, collect()))->values()->all();
            $scopeSnapshots = $this->filterSnapshots($allSnapshots, $groupLearnerIds->all(), $groupLectureIds);
            $scopeMetrics = $this->learningAnalytics->aggregateScopeMetrics($scopeSnapshots, $fifteenDaysAgo);

            $startedCount = (int) ($scopeMetrics['started_users_count'] ?? 0);
            $recentCount = (int) ($scopeMetrics['recent_users_count'] ?? 0);

            $group->last_completed_at = $scopeMetrics['last_activity_at'] ?? null;
            $group->lecons_terminees_count = (int) ($scopeMetrics['completed_count'] ?? 0);
            $group->taux_reussite = (int) ($scopeMetrics['success_rate'] ?? 0);
            $group->not_started_count = max(0, $groupLearnerIds->count() - $startedCount);
            $group->inactive_count = max(0, $startedCount - $recentCount);
            $group->alert_count = $group->not_started_count + $group->inactive_count;

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
            $scopeSnapshots = $this->filterSnapshots($allSnapshots, $stagiaireIds->all(), $moduleLectureIds);
            $scopeMetrics = $this->learningAnalytics->aggregateScopeMetrics($scopeSnapshots);

            $module->stagiaires_count = $stagiaireIds->count();
            $module->groupes_count = (int) DB::table('group_module')
                ->join('groups', 'groups.id', '=', 'group_module.group_id')
                ->where('group_module.module_id', $module->id)
                ->whereIn('groups.id', $accessibleGroupIds->all())
                ->count();
            $module->avg_score = (int) ($scopeMetrics['average_score'] ?? 0);
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
            $module->top_failed_question = $mainDifficulty->question_text ?? null;
            $module->top_failed_rate = $mainDifficulty->fail_rate ?? 0;
            $module->top_failed_failures = (int) ($mainDifficulty->failures ?? 0);

            if ($module->start_rate < 50) {
                $module->attention_label = 'Faible démarrage';
                $module->attention_variant = 'amber';
                $module->attention_detail = $module->stagiaires_count > 0
                    ? $module->started_count . ' stagiaire(s) sur ' . $module->stagiaires_count . ' ont commencé.'
                    : 'Aucun stagiaire affecté pour le moment.';
            } elseif ($module->avg_score < 50) {
                $module->attention_label = 'Résultats faibles';
                $module->attention_variant = 'red';
                $module->attention_detail = 'Le score moyen est en dessous du seuil de réussite.';
            } elseif (! empty($module->top_failed_question)) {
                $module->attention_label = 'Difficultés quiz';
                $module->attention_variant = 'blue';
                $module->attention_detail = 'Question la plus ratée : ' . $module->top_failed_rate . '% d échec.';
            } else {
                $module->attention_label = 'Bon suivi';
                $module->attention_variant = 'green';
                $module->attention_detail = 'Le module démarre correctement et les résultats sont stables.';
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
                    || ! empty($module->top_failed_question);
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
            'day' => now()->addMinutes(2),
            'week' => now()->addMinutes(5),
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
            return [
                'range' => $range,
                'title' => $config['title'],
                'subtitle' => $config['subtitle'],
                'labels' => $config['labels'],
                'full_labels' => $config['full_labels'],
                'visible_label_indexes' => $config['visible_label_indexes'],
                'average_points' => array_fill(0, count($config['bucket_keys']), 0),
                'chart_groups' => [],
                'table_groups' => [],
                'summary' => [
                    'groups_count' => 0,
                    'groups_with_learners_count' => 0,
                    'learners_count' => 0,
                    'current_average_rate' => 0,
                    'peak_average_rate' => 0,
                    'peak_label' => null,
                ],
                'meta' => [
                    'chart_truncated' => false,
                    'empty_message' => 'Aucun groupe n est encore rattaché à votre espace formateur.',
                ],
            ];
        }

        $groupIds = $groups->pluck('id')->all();
        $learnerIdsByGroup = $this->resolveGroupLearnerIds($groupIds);
        $lectureIdsByGroup = $this->resolveGroupLectureIds($groupIds);

        $allLearnerIds = $learnerIdsByGroup
            ->flatten()
            ->unique()
            ->values()
            ->all();

        $allLectureIds = $lectureIdsByGroup
            ->flatten()
            ->unique()
            ->values()
            ->all();

        if ($allLearnerIds === [] || $allLectureIds === []) {
            return [
                'range' => $range,
                'title' => $config['title'],
                'subtitle' => $config['subtitle'],
                'labels' => $config['labels'],
                'full_labels' => $config['full_labels'],
                'visible_label_indexes' => $config['visible_label_indexes'],
                'average_points' => array_fill(0, count($config['bucket_keys']), 0),
                'chart_groups' => [],
                'table_groups' => [],
                'summary' => [
                    'groups_count' => $groups->count(),
                    'groups_with_learners_count' => $groupsWithLearnersCount,
                    'learners_count' => $totalLearners,
                    'current_average_rate' => 0,
                    'peak_average_rate' => 0,
                    'peak_label' => null,
                ],
                'meta' => [
                    'chart_truncated' => false,
                    'empty_message' => $totalLearners === 0
                        ? 'Vos groupes existent déjà, mais aucun stagiaire n y est encore rattaché.'
                        : 'Des stagiaires sont rattachés, mais aucun module ne remonte encore d activite exploitable.',
                ],
            ];
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
            $userId = (int) ($event['user_id'] ?? 0);
            $lectureId = (int) ($event['lecture_id'] ?? 0);
            $activityAt = $event['activity_at'] ?? null;

            if (! $activityAt instanceof Carbon) {
                continue;
            }

            $bucketKey = $activityAt->format($config['bucket_format']);
            if (! in_array($bucketKey, $config['bucket_keys'], true)) {
                continue;
            }

            $matchedGroups = $this->intersectSetKeys(
                $lectureGroupMap[$lectureId] ?? [],
                $userGroupMap[$userId] ?? [],
            );

            foreach ($matchedGroups as $groupId) {
                $bucketMembership[$groupId][$bucketKey][$userId] = true;
            }
        }

        $activityRows = $bucketMembership;

        $tableGroups = $groups
            ->map(function ($group) use ($activityRows, $config) {
                $bucketMap = collect($activityRows[$group->id] ?? []);
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

                $latestRate = count($points) > 0 ? (int) end($points) : 0;
                $averageRate = count($points) > 0 ? (int) round(array_sum($points) / count($points)) : 0;
                $firstRate = $points[0] ?? 0;

                return [
                    'id' => (int) $group->id,
                    'name' => (string) $group->name,
                    'learners_count' => $learnersCount,
                    'points' => $points,
                    'latest_rate' => $latestRate,
                    'average_rate' => $averageRate,
                    'trend' => $latestRate - $firstRate,
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
            'range' => $range,
            'title' => $config['title'],
            'subtitle' => $config['subtitle'],
            'labels' => $config['labels'],
            'full_labels' => $config['full_labels'],
            'visible_label_indexes' => $config['visible_label_indexes'],
            'average_points' => $averagePoints,
            'chart_groups' => $chartGroups,
            'table_groups' => $tableGroups->all(),
            'summary' => [
                'groups_count' => $groups->count(),
                'groups_with_learners_count' => $groupsWithLearnersCount,
                'learners_count' => $totalLearners,
                'current_average_rate' => count($averagePoints) > 0 ? (int) end($averagePoints) : 0,
                'peak_average_rate' => $peakAverageRate,
                'peak_label' => $peakIndex !== false ? ($config['full_labels'][$peakIndex] ?? null) : null,
            ],
            'meta' => [
                'chart_truncated' => $tableGroups->count() > count($chartGroups),
                'empty_message' => $totalLearners === 0
                    ? 'Vos groupes existent déjà, mais aucun stagiaire n y est encore rattaché.'
                    : null,
            ],
        ];
    }

    private function resolveDashboardActivityConfig(string $range): array
    {
        $now = now();

        if ($range === 'day') {
            $startAt = $now->copy()->subHours(23)->startOfHour();
            $endAt = $now->copy()->endOfHour();
            $step = 'hour';
            $format = 'Y-m-d H:00:00';
            $title = 'Activité des groupes';
            $subtitle = 'Lecture heure par heure sur les dernières 24 heures.';
            $visibleLabelIndexes = [0, 3, 6, 9, 12, 15, 18, 21, 23];
        } elseif ($range === 'month') {
            $startAt = $now->copy()->subDays(29)->startOfDay();
            $endAt = $now->copy()->endOfDay();
            $step = 'day';
            $format = 'Y-m-d';
            $title = 'Activité des groupes';
            $subtitle = 'Tendance quotidienne sur les 30 derniers jours.';
            $visibleLabelIndexes = [0, 5, 10, 15, 20, 25, 29];
        } elseif ($range === 'year') {
            $startAt = $now->copy()->subMonths(11)->startOfMonth();
            $endAt = $now->copy()->endOfMonth();
            $step = 'month';
            $format = 'Y-m-01';
            $title = 'Activité des groupes';
            $subtitle = 'Vision mensuelle sur les 12 derniers mois.';
            $visibleLabelIndexes = range(0, 11);
        } else {
            $startAt = $now->copy()->subDays(6)->startOfDay();
            $endAt = $now->copy()->endOfDay();
            $step = 'day';
            $format = 'Y-m-d';
            $title = 'Activité des groupes';
            $subtitle = 'Suivi jour par jour sur les 7 derniers jours.';
            $visibleLabelIndexes = range(0, 6);
        }

        $bucketKeys = [];
        $labels = [];
        $fullLabels = [];
        $cursor = $startAt->copy();

        while ($cursor <= $endAt) {
            $bucketKeys[] = $cursor->format($format);
            $labels[] = $this->formatDashboardActivityShortLabel($cursor, $range);
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
            'title' => $title,
            'subtitle' => $subtitle,
            'query_start_at' => $startAt,
            'query_end_at' => $now,
            'bucket_format' => $format,
            'bucket_keys' => $bucketKeys,
            'labels' => $labels,
            'full_labels' => $fullLabels,
            'visible_label_indexes' => $visibleLabelIndexes,
        ];
    }

    private function formatDashboardActivityShortLabel(Carbon $date, string $range): string
    {
        return match ($range) {
            'day' => $date->translatedFormat('H\h'),
            'year' => ucfirst($date->locale('fr')->translatedFormat('M')),
            default => $date->translatedFormat('d/m'),
        };
    }

    private function formatDashboardActivityFullLabel(Carbon $date, string $range): string
    {
        return match ($range) {
            'day' => $date->translatedFormat('d/m H\h'),
            'year' => ucfirst($date->locale('fr')->translatedFormat('F Y')),
            default => ucfirst($date->locale('fr')->translatedFormat('D d M')),
        };
    }

    private function resolveGroupLearnerIds(array $groupIds): \Illuminate\Support\Collection
    {
        $groupIds = collect($groupIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();

        if ($groupIds === []) {
            return collect();
        }

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

    private function resolveGroupLectureIds(array $groupIds): \Illuminate\Support\Collection
    {
        $groupIds = collect($groupIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();

        if ($groupIds === []) {
            return collect();
        }

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

    private function resolveModuleLectureIds(array $moduleIds): \Illuminate\Support\Collection
    {
        $moduleIds = collect($moduleIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();

        if ($moduleIds === []) {
            return collect();
        }

        $lectureIdsByModule = ModuleLecture::query()
            ->whereIn('module_id', $moduleIds)
            ->get(['id', 'module_id'])
            ->groupBy('module_id')
            ->map(fn ($rows) => $rows->pluck('id')->map(fn ($id) => (int) $id)->unique()->values());

        return collect($moduleIds)
            ->mapWithKeys(fn (int $moduleId) => [$moduleId => $lectureIdsByModule->get($moduleId, collect())]);
    }

    private function filterSnapshots(\Illuminate\Support\Collection $snapshots, array $userIds, array $lectureIds): \Illuminate\Support\Collection
    {
        $userLookup = array_fill_keys(
            collect($userIds)
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id) => $id > 0)
                ->values()
                ->all(),
            true
        );

        $lectureLookup = array_fill_keys(
            collect($lectureIds)
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id) => $id > 0)
                ->values()
                ->all(),
            true
        );

        if ($userLookup === [] || $lectureLookup === []) {
            return collect();
        }

        return $snapshots->filter(function (array $snapshot) use ($lectureLookup, $userLookup) {
            return isset($userLookup[(int) ($snapshot['user_id'] ?? 0)])
                && isset($lectureLookup[(int) ($snapshot['lecture_id'] ?? 0)]);
        })->values();
    }

    private function intersectSetKeys(array $left, array $right): array
    {
        if ($left === [] || $right === []) {
            return [];
        }

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

    /* -------------------------------------------------------------------------
     | Auth / Déconnexion Formateur
     |-------------------------------------------------------------------------- */
    public function FormateurLogout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /* -------------------------------------------------------------------------
     | Profil Formateur
     |-------------------------------------------------------------------------- */
    public function FormateurProfile()
    {
        $id = Auth::user()->id;
        $profileData = User::findOrFail($id);

        return view('formateur.profile_view', compact('profileData'));
    }

    public function FormateurParametre()
    {
        $id = Auth::user()->id;
        $profileData = User::findOrFail($id);

        return view('formateur.parametre', compact('profileData'));
    }

    public function FormateurProfilStore(Request $request)
    {
        $id = Auth::user()->id;
        $user = User::findOrFail($id);

        $request->validate([
            'name'     => 'nullable|string|max:255',
            'prenom'   => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255',
            'email'    => 'nullable|email|max:255',
            'phone'    => 'nullable|string|max:30',
            'photo'    => 'nullable|image|max:2048',
        ]);

        $user->name = $request->name;
        $user->prenom = $request->prenom;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->phone = $request->phone;

        if ($request->file('photo')) {
            $file = $request->file('photo');
            $filename = date('YmdHi') . '_' . $file->getClientOriginalName();
            $file->move(public_path('/upload/formateur_images'), $filename);
            $user->photo = $filename;
        }

        $user->save();

        return redirect()
            ->route('formateur.parametre')
            ->with('message', 'Profil mis à jour avec succès.');
    }

    /* -------------------------------------------------------------------------
     | Sécurité Formateur
     |-------------------------------------------------------------------------- */
    public function showFormateurSecurite()
    {
        $user = Auth::user();

        return view('formateur.securite', compact('user'));
    }

    public function FormateurSecurite(Request $request)
    {
        $id = Auth::user()->id;
        $user = User::findOrFail($id);

        $request->validate([
            'currentPassword' => 'required',
            'newPassword'     => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->currentPassword, $user->password)) {
            return back()->withErrors([
                'currentPassword' => 'Le mot de passe actuel est incorrect.',
            ]);
        }

        $user->password = Hash::make($request->newPassword);
        $user->save();

        return back()->with('message', 'Votre mot de passe a été modifié avec succès.');
    }

    public function destroyOwnAccount(Request $request)
    {
        $user = Auth::user();

        abort_unless($user && $user->role === 'formateur', 403);

        $validator = Validator::make(
            $request->all(),
            [
                'password' => ['required', 'string'],
            ],
            [
                'password.required' => 'Le mot de passe actuel est requis pour confirmer la suppression.',
            ]
        );

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'accountDeletion')
                ->with('openDeleteAccountModal', true);
        }

        if (! Hash::check((string) $request->input('password'), (string) $user->password)) {
            return back()
                ->withErrors([
                    'password' => 'Le mot de passe actuel est incorrect.',
                ], 'accountDeletion')
                ->with('openDeleteAccountModal', true);
        }

        try {
            $user->delete();
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withErrors([
                    'password' => 'La suppression du compte a échoué. Merci de réessayer ou de contacter le support.',
                ], 'accountDeletion')
                ->with('openDeleteAccountModal', true);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('index')
            ->with('success', 'Votre compte formateur a été supprimé définitivement.');
    }

    /* -------------------------------------------------------------------------
     | Stagiaires
     |-------------------------------------------------------------------------- */
    public function indexStagiaires(Request $request)
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);
        $allowedPerPage = [10, 25, 50, 100];
        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        // Liste des groupes du formateur (pour filtre)
        $groupes = Group::query()
            ->whereIn('id', $accessibleGroupIds->all())
            ->orderBy('name')
            ->get(['id', 'name']);

        $query = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($q) use ($accessibleGroupIds, $formateurId) {
                $q->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($gq) use ($accessibleGroupIds) {
                        $gq->whereIn('groups.id', $accessibleGroupIds->all());
                    });
            });

        // Filtre groupe (sécurisé sur le périmètre du formateur)
        if ($groupId = $request->input('group_id')) {
            $query->whereHas('groupesStagiaire', function ($gq) use ($groupId, $accessibleGroupIds) {
                $gq->where('groups.id', $groupId)
                    ->whereIn('groups.id', $accessibleGroupIds->all());
            });
        }

        // Recherche texte
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('prenom', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $stagiaires = $query
            ->with(['groupesStagiaire' => function ($q) use ($accessibleGroupIds) {
                $q->whereIn('groups.id', $accessibleGroupIds->all())->orderBy('name');
            }])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('formateur.backend.stagiaires.all_stagiaires', compact('stagiaires', 'groupes', 'perPage', 'allowedPerPage'));
    }

    public function createStagiaire()
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);

        $groupes = Group::query()
            ->whereIn('id', $accessibleGroupIds->all())
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedGroupId = request()->integer('group_id') ?: null;

        if ($selectedGroupId && ! $groupes->contains('id', $selectedGroupId)) {
            $selectedGroupId = null;
        }

        return view('formateur.backend.stagiaires.add_stagiaire', compact('groupes', 'selectedGroupId'));
    }

    public function storeStagiaire(Request $request)
    {
        $request->validate([
            'prenom'   => ['required', 'string', 'max:255'],
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'group_id' => ['nullable', 'integer', 'exists:groups,id'],
        ]);

        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);
        $email = strtolower(trim($request->email));
        $prenom = $request->prenom;
        $nom = $request->name;
        $gid = $request->integer('group_id') ?: null;

        // Si un groupe est fourni, il doit appartenir au formateur
        $group = null;
        if ($gid) {
            $group = Group::query()
                ->where('id', $gid)
                ->whereIn('id', $accessibleGroupIds->all())
                ->firstOrFail();
        }

        // Réutilisation possible (y compris supprimé), mais seulement si le compte est bien un stagiaire
        $user = User::withTrashed()->where('email', $email)->first();

        if ($user && $user->role !== 'stagiaire') {
            return back()
                ->withErrors(['email' => 'Adresse déjà utilisée par un autre type de compte.'])
                ->withInput();
        }

        if ($user) {
            if ($user->trashed()) {
                $user->restore();
            }

            // On rattache au formateur si pas déjà défini
            if (!$user->formateur_id) {
                $user->formateur_id = $formateurId;
            }

            // On complète sans écraser inutilement
            $user->prenom = $user->prenom ?: $prenom;
            $user->name = $user->name ?: $nom;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();
        } else {
            $user = User::create([
                'prenom'       => $prenom,
                'name'         => $nom,
                'email'        => $email,
                'password'     => $request->filled('password')
                    ? Hash::make($request->password)
                    : bcrypt(str()->password(12)),
                'role'         => 'stagiaire',
                'formateur_id' => $formateurId,
                'status'       => 1,
                'code_acces'   => CodeGeneratorService::generateUniqueAccessCode(),
            ]);
        }

        if ($group) {
            $group->students()->syncWithoutDetaching([
                $user->id => ['role_in_group' => 'stagiaire'],
            ]);
        }

        return redirect()
            ->route('formateur.stagiaires.index')
            ->with('success', $user->wasRecentlyCreated
                ? 'Stagiaire créé et rattaché si un groupe a été fourni.'
                : 'Stagiaire existant réutilisé et rattaché si un groupe a été fourni.');
    }

    public function editStagiaire($id)
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);

        $groupes = Group::query()
            ->whereIn('id', $accessibleGroupIds->all())
            ->orderBy('name')
            ->get(['id', 'name']);

        $stagiaire = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($query) use ($accessibleGroupIds, $formateurId) {
                $query->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($q) use ($accessibleGroupIds) {
                        $q->whereIn('groups.id', $accessibleGroupIds->all());
                    });
            })
            ->with(['groupesStagiaire' => function ($query) use ($accessibleGroupIds) {
                $query->whereIn('groups.id', $accessibleGroupIds->all())->orderBy('name');
            }])
            ->findOrFail($id);

        return view('formateur.backend.stagiaires.edit_stagiaire', compact('stagiaire', 'groupes'));
    }

    public function updateStagiaire(Request $request, $id)
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);

        $stagiaire = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($query) use ($accessibleGroupIds, $formateurId) {
                $query->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($q) use ($accessibleGroupIds) {
                        $q->whereIn('groups.id', $accessibleGroupIds->all());
                    });
            })
            ->findOrFail($id);

        $request->validate([
            'prenom'   => 'required|string|max:255',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $stagiaire->id,
            'password' => 'nullable|string|min:8',
            'group_ids' => ['nullable', 'array'],
            'group_ids.*' => [
                'integer',
                Rule::exists('groups', 'id')->where(fn ($query) => $query->whereIn('id', $accessibleGroupIds->all())),
            ],
        ]);

        $selectedGroupIds = collect($request->input('group_ids', []))
            ->map(fn ($groupId) => (int) $groupId)
            ->filter()
            ->unique()
            ->values();

        $trainerGroupIds = Group::query()
            ->whereIn('id', $accessibleGroupIds->all())
            ->pluck('id')
            ->map(fn ($groupId) => (int) $groupId);

        DB::transaction(function () use ($request, $stagiaire, $trainerGroupIds, $selectedGroupIds): void {
            $stagiaire->prenom = $request->prenom;
            $stagiaire->name = $request->name;
            $stagiaire->email = strtolower(trim((string) $request->email));

            if ($request->filled('password')) {
                $stagiaire->password = Hash::make($request->password);
            }

            $stagiaire->save();

            if ($trainerGroupIds->isEmpty()) {
                return;
            }

            DB::table('group_user')
                ->where('user_id', $stagiaire->id)
                ->where('role_in_group', 'stagiaire')
                ->whereIn('group_id', $trainerGroupIds->all())
                ->delete();

            foreach ($selectedGroupIds as $groupId) {
                DB::table('group_user')->updateOrInsert(
                    [
                        'group_id' => $groupId,
                        'user_id' => $stagiaire->id,
                        'role_in_group' => 'stagiaire',
                    ],
                    []
                );
            }
        });

        return redirect()
            ->route('formateur.stagiaires.index')
            ->with('success', 'Stagiaire modifié avec succès.');
    }

    public function destroyStagiaire($id)
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);

        $stagiaire = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($query) use ($accessibleGroupIds, $formateurId) {
                $query->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($q) use ($accessibleGroupIds) {
                        $q->whereIn('groups.id', $accessibleGroupIds->all());
                    });
            })
            ->findOrFail($id);

        $stagiaire->delete();

        return redirect()
            ->route('formateur.stagiaires.index')
            ->with('success', 'Stagiaire supprimé avec succès.');
    }

    /* -------------------------------------------------------------------------
     | Inscription formateur
     |-------------------------------------------------------------------------- */
    public function showRegistrationForm()
    {
        return view('formateur.auth.register');
    }

    public function register(Request $request)
    {
        // Piège antispam
        if ($request->filled('website')) {
            return back()->withErrors(['form' => 'Envoi invalide.'])->withInput();
        }

        $validated = $request->validate([
            'prenom'   => 'required|string|max:255',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone'    => 'nullable|string|max:30',
            'societe'  => 'nullable|string|max:150',
            'address'  => 'nullable|string|max:255',
            'g-recaptcha-response' => 'required|captcha',
        ]);

        $formateur = User::create([
            'prenom'   => $validated['prenom'],
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'address'  => $validated['address'] ?? null,
            'societe'  => $validated['societe'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => 'formateur',
        ]);

        Auth::login($formateur);

        Mail::to($formateur->email)->send(new FormateurWelcome([
            'prenom' => $formateur->prenom,
            'nom'    => $formateur->name,
            'email'  => $formateur->email,
        ]));

        Mail::to('contact@oneduc.fr')->send(new NewFormateurNotification([
            'prenom'  => $formateur->prenom,
            'nom'     => $formateur->name,
            'email'   => $formateur->email,
            'phone'   => $formateur->phone,
            'societe' => $formateur->societe,
        ]));

        return redirect()
            ->route('formateur.dashboard')
            ->with('success', 'Bienvenue sur Oneduc !');
    }

    /* -------------------------------------------------------------------------
     | Mes modules (index)
     |-------------------------------------------------------------------------- */
    public function mesModules(Request $request)
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);
        $search = trim((string) $request->query('search', ''));

        $modules = Module::query()
            ->where(function ($q) use ($accessibleGroupIds, $formateurId) {
                $q->whereHas('groups', function ($g) use ($accessibleGroupIds) {
                    $g->whereIn('groups.id', $accessibleGroupIds->all());
                })
                ->orWhere('formateur_id', $formateurId);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('module_title', 'like', "%{$search}%")
                      ->orWhere('module_name', 'like', "%{$search}%");
                });
            })
            ->with([
                'sections' => function ($q) {
                    $q->select('id', 'module_id')->orderBy('id');
                },
                'groups' => function ($q) use ($accessibleGroupIds) {
                    $q->whereIn('groups.id', $accessibleGroupIds->all())
                        ->with(['users' => function ($u) {
                            $u->where('role', 'stagiaire');
                        }]);
                },
            ])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('formateur.formations.index', compact('modules', 'search'));
    }

    /* -------------------------------------------------------------------------
     | Détail module (formateur)
     |-------------------------------------------------------------------------- */
    public function moduleDetail(Request $request, Module $module)
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);

        $isAllowed = ($module->formateur_id === $formateurId)
            || $module->groups()->whereIn('groups.id', $accessibleGroupIds->all())->exists();

        abort_unless($isAllowed, 403);

        $module->load([
            'formateur',
            'sections' => function ($query) {
                $query->orderBy('id')
                    ->with(['lectures' => function ($lectureQuery) {
                        $lectureQuery->orderBy('position')
                            ->orderBy('id')
                            ->with(['objectives' => function ($objectiveQuery) {
                                $objectiveQuery->orderBy('position')->orderBy('id');
                            }]);
                    }]);
            },
            'groups' => function ($q) use ($accessibleGroupIds) {
                $q->whereIn('groups.id', $accessibleGroupIds->all())
                    ->with(['users' => function ($u) {
                        $u->where('role', 'stagiaire');
                    }]);
            },
        ]);

        $mode = (string) $request->query('mode', 'officiel');
        $groupId = $this->resolveTrainerModuleDetailGroupId($request, $module, $formateurId);

        if ($mode !== 'officiel') {
            $this->applyTrainerGroupLessonOverrides($module, $groupId);
        }

        $contextQuery = array_filter([
            'mode' => $mode !== 'officiel' ? $mode : null,
            'group_id' => $mode !== 'officiel' ? ($groupId ?: null) : null,
        ]);

        $totalSections = $module->sections->count();
        $totalLectures = $module->sections->flatMap->lectures->count();
        $totalSlides = (int) $module->sections->flatMap->lectures->sum('slide_count');
        $totalQuestions = (int) $module->sections->flatMap->lectures->sum('quiz_questions_per_attempt');

        $groupCount = $module->groups->count();
        $stagiaires = $module->groups->flatMap(fn ($g) => $g->users)->unique('id')->values();
        $stagiaireCount = $stagiaires->count();

        // Objectifs pédagogiques issus des leçons (agrégés et sans doublon)
        $lessonObjectives = $module->sections
            ->flatMap->lectures
            ->flatMap(function ($lecture) {
                return $lecture->objectives->pluck('title');
            })
            ->map(fn ($title) => trim((string) $title))
            ->filter()
            ->unique()
            ->values();

        return view('formateur.formations.formateur_module_detail', compact(
            'module',
            'totalSections',
            'totalLectures',
            'totalSlides',
            'totalQuestions',
            'groupCount',
            'stagiaires',
            'stagiaireCount',
            'lessonObjectives',
            'contextQuery'
        ));
    }

    private function resolveTrainerModuleDetailGroupId(Request $request, Module $module, int $formateurId): ?int
    {
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);
        $forcedGroupId = (int) $request->query('group_id', 0);

        if ($forcedGroupId > 0) {
            $isAccessibleGroup = Group::query()
                ->where('id', $forcedGroupId)
                ->whereIn('id', $accessibleGroupIds->all())
                ->exists();

            if (! $isAccessibleGroup) {
                return null;
            }

            $hasModule = DB::table('group_module')
                ->where('group_id', $forcedGroupId)
                ->where('module_id', $module->id)
                ->exists();

            return $hasModule ? $forcedGroupId : null;
        }

        return Group::query()
            ->whereIn('id', $accessibleGroupIds->all())
            ->whereHas('modules', fn ($query) => $query->where('modules.id', $module->id))
            ->value('id');
    }

    private function applyTrainerGroupLessonOverrides(Module $module, ?int $groupId): void
    {
        if (! $groupId || ! $module->relationLoaded('sections')) {
            return;
        }

        $overrides = \App\Models\GroupModuleLecture::query()
            ->where('group_id', $groupId)
            ->where('module_id', $module->id)
            ->get()
            ->keyBy('lecture_id');

        if ($overrides->isEmpty()) {
            return;
        }

        $module->sections->each(function ($section) use ($overrides): void {
            $lectures = collect($section->lectures)
                ->filter(function ($lecture) use ($overrides) {
                    $row = $overrides->get($lecture->id);

                    return $row ? (bool) $row->is_enabled : true;
                })
                ->sortBy(function ($lecture) use ($overrides) {
                    $row = $overrides->get($lecture->id);

                    return $row ? (int) $row->position : (int) $lecture->position;
                })
                ->values();

            $section->setRelation('lectures', $lectures);
        });
    }
public function updateQuizCount(Request $request, $lectureId)
{
    $lecture = ModuleLecture::findOrFail($lectureId);

    // Validation : on doit envoyer un nombre, et il ne peut pas dépasser le total dispo
    $totalQuestionsInBank = $lecture->quizQuestions()->count();

    $validated = $request->validate([
        'questions_count' => 'required|integer|min:1|max:' . ($totalQuestionsInBank > 0 ? $totalQuestionsInBank : 1),
    ]);

    // Mise à jour
    $lecture->update([
        'quiz_questions_per_attempt' => $validated['questions_count']
    ]);

    return back()->with('success', 'Le nombre de questions a été mis à jour.');
}
    /* -------------------------------------------------------------------------
     | Prévisualisation (mode test)
     |-------------------------------------------------------------------------- */
    public function preview(Module $module)
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);

        // Sécuriser l'accès au module
        $isAllowed = ($module->formateur_id === $formateurId)
            || $module->groups()->whereIn('groups.id', $accessibleGroupIds->all())->exists();

        abort_unless($isAllowed, 403);

        $module->load('sections.lectures');

        $firstSection = $module->sections->first();
        $firstLecture = $firstSection?->lectures->first();

        if (!$firstSection || !$firstLecture) {
            return back()->with('error', 'Aucune leçon disponible à tester.');
        }

        // Attention : cette route est côté stagiaire. À conserver seulement si c'est voulu.
        return redirect()->route('formateur.formations.lecture', [
            'module'  => $module->id,
            'section' => $firstSection->id,
            'lecture' => $firstLecture->id,
        ]);
    }
}
