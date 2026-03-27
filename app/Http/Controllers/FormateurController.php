<?php

namespace App\Http\Controllers;

// /home/laurents/Oneduc_Dev/app/Http/Controllers/FormateurController.php

use Carbon\Carbon;
use App\Mail\FormateurWelcome;
use App\Mail\NewFormateurNotification;
use App\Models\Group;
use App\Models\Module;
use App\Models\ScormScore;
use App\Models\User;
use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use App\Models\ModuleLecture;


class FormateurController extends Controller
{
    /* -------------------------------------------------------------------------
     | Tableau de bord Formateur
     |-------------------------------------------------------------------------- */
    public function FormateurDashboard()
    {
        $formateurId = auth()->id();
        $fifteenDaysAgo = now()->subDays(15);

        $groupCount = Group::query()
            ->where('instructor_id', $formateurId)
            ->count();

        $modulesUsed = Module::query()
            ->whereHas('groups', function ($q) use ($formateurId) {
                $q->where('instructor_id', $formateurId);
            })
            ->distinct('modules.id')
            ->count('modules.id');

        $learnerIds = User::query()
            ->where('role', 'stagiaire')
            ->whereHas('groupesStagiaire', function ($q) use ($formateurId) {
                $q->where('instructor_id', $formateurId);
            })
            ->distinct()
            ->pluck('users.id');

        $learnerCount = $learnerIds->count();

        // Score moyen (ce n'est pas un taux d'achèvement)
        $avgScore = ScormScore::query()
            ->whereHas('lecture.module.groups', function ($q) use ($formateurId) {
                $q->where('instructor_id', $formateurId);
            })
            ->whereHas('user', function ($q) {
                $q->where('role', 'stagiaire');
            })
            ->avg('last_score');

        $avgScoreRounded = $avgScore ? (int) round($avgScore) : 0;
        $avgCompletion = $avgScoreRounded;

        $activeLearnerIds = collect();
        $recentLearnerIds = collect();

        if ($learnerIds->isNotEmpty()) {
            $activeLearnerIds = DB::table('progressions')
                ->whereIn('user_id', $learnerIds)
                ->distinct()
                ->pluck('user_id');

            $recentLearnerIds = DB::table('progressions')
                ->whereIn('user_id', $learnerIds)
                ->whereNotNull('completed_at')
                ->where('completed_at', '>=', $fifteenDaysAgo)
                ->distinct()
                ->pluck('user_id');
        }

        $notStartedLearnersCount = $learnerIds->diff($activeLearnerIds)->count();
        $inactiveLearnersCount = $activeLearnerIds->diff($recentLearnerIds)->count();

        // Groupes affichés dans la section "Suivi par groupes" du dashboard
        $groupesDashboard = Group::query()
            ->where('instructor_id', $formateurId)
            ->withCount([
                'students as stagiaires_count' => function ($q) {
                    $q->where('role', 'stagiaire');
                },
                'modules as modules_count',
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'created_at']);

        $groupIds = $groupesDashboard->pluck('id')->all();

        $lastActivityByGroup = collect();
        $scoresByGroup = collect();
        $learnerIdsByGroup = collect();
        $activeLearnerIdsByGroup = collect();
        $recentLearnerIdsByGroup = collect();

        if (!empty($groupIds)) {
            $learnerIdsByGroup = DB::table('group_user')
                ->join('users', 'users.id', '=', 'group_user.user_id')
                ->whereIn('group_user.group_id', $groupIds)
                ->where('group_user.role_in_group', 'stagiaire')
                ->where('users.role', 'stagiaire')
                ->select('group_user.group_id', 'group_user.user_id')
                ->get()
                ->groupBy('group_id')
                ->map(fn ($rows) => $rows->pluck('user_id')->unique()->values());

            $activeLearnerIdsByGroup = DB::table('group_user')
                ->join('users', 'users.id', '=', 'group_user.user_id')
                ->join('progressions', 'progressions.user_id', '=', 'group_user.user_id')
                ->whereIn('group_user.group_id', $groupIds)
                ->where('group_user.role_in_group', 'stagiaire')
                ->where('users.role', 'stagiaire')
                ->select('group_user.group_id', 'group_user.user_id')
                ->distinct()
                ->get()
                ->groupBy('group_id')
                ->map(fn ($rows) => $rows->pluck('user_id')->unique()->values());

            $recentLearnerIdsByGroup = DB::table('group_user')
                ->join('users', 'users.id', '=', 'group_user.user_id')
                ->join('progressions', 'progressions.user_id', '=', 'group_user.user_id')
                ->whereIn('group_user.group_id', $groupIds)
                ->where('group_user.role_in_group', 'stagiaire')
                ->where('users.role', 'stagiaire')
                ->whereNotNull('progressions.completed_at')
                ->where('progressions.completed_at', '>=', $fifteenDaysAgo)
                ->select('group_user.group_id', 'group_user.user_id')
                ->distinct()
                ->get()
                ->groupBy('group_id')
                ->map(fn ($rows) => $rows->pluck('user_id')->unique()->values());

            $lastActivityByGroup = DB::table('progressions')
                ->join('group_user', 'group_user.user_id', '=', 'progressions.user_id')
                ->join('users', 'users.id', '=', 'group_user.user_id')
                ->whereIn('group_user.group_id', $groupIds)
                ->where('group_user.role_in_group', 'stagiaire')
                ->where('users.role', 'stagiaire')
                ->selectRaw('group_user.group_id, MAX(progressions.completed_at) as last_completed_at')
                ->groupBy('group_user.group_id')
                ->pluck('last_completed_at', 'group_user.group_id');

            $scoresByGroup = DB::table('progressions')
                ->join('group_user', 'group_user.user_id', '=', 'progressions.user_id')
                ->join('users', 'users.id', '=', 'group_user.user_id')
                ->leftJoin('scorm_scores', function ($join) {
                    $join->on('scorm_scores.user_id', '=', 'progressions.user_id')
                        ->on('scorm_scores.lecture_id', '=', 'progressions.lecture_id');
                })
                ->whereIn('group_user.group_id', $groupIds)
                ->where('group_user.role_in_group', 'stagiaire')
                ->where('users.role', 'stagiaire')
                ->selectRaw('
                    group_user.group_id,
                    COUNT(progressions.id) as total_lessons,
                    SUM(CASE WHEN scorm_scores.last_score >= 50 THEN 1 ELSE 0 END) as success_lessons
                ')
                ->groupBy('group_user.group_id')
                ->get()
                ->keyBy('group_id');
        }

        $groupesDashboard = $groupesDashboard->map(function ($g) use ($lastActivityByGroup, $scoresByGroup, $learnerIdsByGroup, $activeLearnerIdsByGroup, $recentLearnerIdsByGroup) {
            $groupLearnerIds = collect($learnerIdsByGroup->get($g->id, collect()));
            $activeIds = collect($activeLearnerIdsByGroup->get($g->id, collect()));
            $recentIds = collect($recentLearnerIdsByGroup->get($g->id, collect()));

            $g->last_completed_at = $lastActivityByGroup[$g->id] ?? null;
            $g->not_started_count = max(0, $groupLearnerIds->count() - $activeIds->count());
            $g->inactive_count = max(0, $activeIds->diff($recentIds)->count());
            $g->alert_count = $g->not_started_count + $g->inactive_count;

            $agg = $scoresByGroup->get($g->id);
            $total = (int) ($agg->total_lessons ?? 0);
            $success = (int) ($agg->success_lessons ?? 0);
            $g->taux_reussite = $total > 0 ? (int) round(($success / $total) * 100) : 0;

            return $g;
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
                $q->where('instructor_id', $formateurId);
            })
            ->withCount('lectures')
            ->orderBy('module_title')
            ->get(['id', 'module_title']);

        $moduleInsights = $moduleInsights->map(function ($module) use ($formateurId) {
            $stagiaireIds = DB::table('group_module')
                ->join('group_user', 'group_user.group_id', '=', 'group_module.group_id')
                ->join('groups', 'groups.id', '=', 'group_module.group_id')
                ->join('users', 'users.id', '=', 'group_user.user_id')
                ->where('group_module.module_id', $module->id)
                ->where('groups.instructor_id', $formateurId)
                ->where('group_user.role_in_group', 'stagiaire')
                ->where('users.role', 'stagiaire')
                ->pluck('users.id')
                ->unique()
                ->values();

            $module->stagiaires_count = $stagiaireIds->count();
            $module->groupes_count = (int) DB::table('group_module')
                ->join('groups', 'groups.id', '=', 'group_module.group_id')
                ->where('group_module.module_id', $module->id)
                ->where('groups.instructor_id', $formateurId)
                ->count();

            $avgScore = 0;
            if ($stagiaireIds->isNotEmpty()) {
                $avgScore = DB::table('scorm_scores')
                    ->join('module_lectures', 'module_lectures.id', '=', 'scorm_scores.lecture_id')
                    ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
                    ->where('module_sections.module_id', $module->id)
                    ->whereIn('scorm_scores.user_id', $stagiaireIds)
                    ->avg('scorm_scores.last_score');
            }

            $module->avg_score = (int) round($avgScore ?? 0);

            $startedUsers = 0;
            $topFailed = collect();

            if ($stagiaireIds->isNotEmpty()) {
                $startedUsers = DB::table('progressions')
                    ->join('module_lectures', 'module_lectures.id', '=', 'progressions.lecture_id')
                    ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
                    ->where('module_sections.module_id', $module->id)
                    ->whereIn('progressions.user_id', $stagiaireIds)
                    ->distinct('progressions.user_id')
                    ->count('progressions.user_id');

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

            $module->started_count = $startedUsers;
            $module->start_rate = $module->stagiaires_count > 0
                ? (int) round(($startedUsers / $module->stagiaires_count) * 100)
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
            'avgScoreRounded',
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

        $groups = Group::query()
            ->where('instructor_id', $formateurId)
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

        $activityRows = DB::table('progressions')
            ->join('group_user', 'group_user.user_id', '=', 'progressions.user_id')
            ->join('users', 'users.id', '=', 'group_user.user_id')
            ->whereIn('group_user.group_id', $groupIds)
            ->where('group_user.role_in_group', 'stagiaire')
            ->where('users.role', 'stagiaire')
            ->whereNotNull('progressions.completed_at')
            ->whereBetween('progressions.completed_at', [
                $config['query_start_at']->toDateTimeString(),
                $config['query_end_at']->toDateTimeString(),
            ])
            ->selectRaw("group_user.group_id, {$config['bucket_sql']} as bucket_key, COUNT(DISTINCT progressions.user_id) as active_users")
            ->groupBy('group_user.group_id', DB::raw($config['bucket_sql']))
            ->get()
            ->groupBy('group_id')
            ->map(fn ($rows) => $rows->pluck('active_users', 'bucket_key'));

        $tableGroups = $groups
            ->map(function ($group) use ($activityRows, $config) {
                $bucketMap = collect($activityRows->get($group->id, collect()));
                $learnersCount = (int) $group->learners_count;
                $points = [];

                foreach ($config['bucket_keys'] as $bucketKey) {
                    $activeUsers = (int) ($bucketMap[$bucketKey] ?? 0);
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
            $bucketSql = "DATE_FORMAT(progressions.completed_at, '%Y-%m-%d %H:00:00')";
            $title = 'Activité des groupes';
            $subtitle = 'Lecture heure par heure sur les dernières 24 heures.';
            $visibleLabelIndexes = [0, 3, 6, 9, 12, 15, 18, 21, 23];
        } elseif ($range === 'month') {
            $startAt = $now->copy()->subDays(29)->startOfDay();
            $endAt = $now->copy()->endOfDay();
            $step = 'day';
            $format = 'Y-m-d';
            $bucketSql = 'DATE(progressions.completed_at)';
            $title = 'Activité des groupes';
            $subtitle = 'Tendance quotidienne sur les 30 derniers jours.';
            $visibleLabelIndexes = [0, 5, 10, 15, 20, 25, 29];
        } elseif ($range === 'year') {
            $startAt = $now->copy()->subMonths(11)->startOfMonth();
            $endAt = $now->copy()->endOfMonth();
            $step = 'month';
            $format = 'Y-m-01';
            $bucketSql = "DATE_FORMAT(progressions.completed_at, '%Y-%m-01')";
            $title = 'Activité des groupes';
            $subtitle = 'Vision mensuelle sur les 12 derniers mois.';
            $visibleLabelIndexes = range(0, 11);
        } else {
            $startAt = $now->copy()->subDays(6)->startOfDay();
            $endAt = $now->copy()->endOfDay();
            $step = 'day';
            $format = 'Y-m-d';
            $bucketSql = 'DATE(progressions.completed_at)';
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
            'bucket_sql' => $bucketSql,
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
            return back()->with('error', 'Le mot de passe actuel est incorrect.');
        }

        $user->password = Hash::make($request->newPassword);
        $user->save();

        return back()->with('message', 'Votre mot de passe a été modifié avec succès.');
    }

    /* -------------------------------------------------------------------------
     | Stagiaires
     |-------------------------------------------------------------------------- */
    public function indexStagiaires(Request $request)
    {
        $formateurId = auth()->id();
        $allowedPerPage = [10, 25, 50, 100];
        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        // Liste des groupes du formateur (pour filtre)
        $groupes = Group::query()
            ->where('instructor_id', $formateurId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $query = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($q) use ($formateurId) {
                $q->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($gq) use ($formateurId) {
                        $gq->where('instructor_id', $formateurId);
                    });
            });

        // Filtre groupe (sécurisé sur le périmètre du formateur)
        if ($groupId = $request->input('group_id')) {
            $query->whereHas('groupesStagiaire', function ($gq) use ($groupId, $formateurId) {
                $gq->where('groups.id', $groupId)
                    ->where('instructor_id', $formateurId);
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
            ->with(['groupesStagiaire' => function ($q) use ($formateurId) {
                $q->where('instructor_id', $formateurId)->orderBy('name');
            }])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('formateur.backend.stagiaires.all_stagiaires', compact('stagiaires', 'groupes', 'perPage', 'allowedPerPage'));
    }

    public function createStagiaire()
    {
        $formateurId = auth()->id();

        $groupes = Group::query()
            ->where('instructor_id', $formateurId)
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
        $email = strtolower(trim($request->email));
        $prenom = $request->prenom;
        $nom = $request->name;
        $gid = $request->integer('group_id') ?: null;

        // Si un groupe est fourni, il doit appartenir au formateur
        $group = null;
        if ($gid) {
            $group = Group::query()
                ->where('id', $gid)
                ->where('instructor_id', $formateurId)
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

        $groupes = Group::query()
            ->where('instructor_id', $formateurId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $stagiaire = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($query) use ($formateurId) {
                $query->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($q) use ($formateurId) {
                        $q->where('instructor_id', $formateurId);
                    });
            })
            ->with(['groupesStagiaire' => function ($query) use ($formateurId) {
                $query->where('instructor_id', $formateurId)->orderBy('name');
            }])
            ->findOrFail($id);

        return view('formateur.backend.stagiaires.edit_stagiaire', compact('stagiaire', 'groupes'));
    }

    public function updateStagiaire(Request $request, $id)
    {
        $formateurId = auth()->id();

        $stagiaire = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($query) use ($formateurId) {
                $query->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($q) use ($formateurId) {
                        $q->where('instructor_id', $formateurId);
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
                Rule::exists('groups', 'id')->where(fn ($query) => $query->where('instructor_id', $formateurId)),
            ],
        ]);

        $selectedGroupIds = collect($request->input('group_ids', []))
            ->map(fn ($groupId) => (int) $groupId)
            ->filter()
            ->unique()
            ->values();

        $trainerGroupIds = Group::query()
            ->where('instructor_id', $formateurId)
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

        $stagiaire = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($query) use ($formateurId) {
                $query->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($q) use ($formateurId) {
                        $q->where('instructor_id', $formateurId);
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
        $search = trim((string) $request->query('search', ''));

        $modules = Module::query()
            ->where(function ($q) use ($formateurId) {
                $q->whereHas('groups', function ($g) use ($formateurId) {
                    $g->where('instructor_id', $formateurId);
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
                'groups' => function ($q) use ($formateurId) {
                    $q->where('instructor_id', $formateurId)
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

        $isAllowed = ($module->formateur_id === $formateurId)
            || $module->groups()->where('instructor_id', $formateurId)->exists();

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
            'groups' => function ($q) use ($formateurId) {
                $q->where('instructor_id', $formateurId)
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
        $forcedGroupId = (int) $request->query('group_id', 0);

        if ($forcedGroupId > 0) {
            $isOwnedGroup = Group::query()
                ->where('id', $forcedGroupId)
                ->where('instructor_id', $formateurId)
                ->exists();

            if (! $isOwnedGroup) {
                return null;
            }

            $hasModule = DB::table('group_module')
                ->where('group_id', $forcedGroupId)
                ->where('module_id', $module->id)
                ->exists();

            return $hasModule ? $forcedGroupId : null;
        }

        return Group::query()
            ->where('instructor_id', $formateurId)
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

        // Sécuriser l'accès au module
        $isAllowed = ($module->formateur_id === $formateurId)
            || $module->groups()->where('instructor_id', $formateurId)->exists();

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
