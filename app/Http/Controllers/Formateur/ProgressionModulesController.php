<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Formateur\Concerns\ProgressionHelpers;
use App\Models\Module;
use App\Services\LearningAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgressionModulesController extends Controller
{
    use ProgressionHelpers;

    public function __construct(
        private readonly LearningAnalyticsService $learningAnalytics,
    ) {
    }

    public function index(Request $request)
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);

        $modules = Module::query()
            ->whereHas('groups', fn($q) => $q->whereIn('groups.id', $accessibleGroupIds->all()))
            ->withCount(['lectures as lectures_count'])
            ->orderBy('module_title')
            ->get();

        $moduleIds = $modules->pluck('id')->all();

        if ($moduleIds === []) {
            return view('formateur.progressions.modules', ['modules' => $modules]);
        }

        $lectureIdsByModule = $this->resolveModuleLectureIds($moduleIds);

        // 1 seule requête pour tous les stagiaires par module (remplace N requêtes)
        $stagiaireIdsByModule = DB::table('group_module')
            ->join('group_user', 'group_user.group_id', '=', 'group_module.group_id')
            ->join('groups', 'groups.id', '=', 'group_module.group_id')
            ->join('users', 'users.id', '=', 'group_user.user_id')
            ->whereIn('group_module.module_id', $moduleIds)
            ->whereIn('groups.id', $accessibleGroupIds->all())
            ->where('users.role', 'stagiaire')
            ->select('group_module.module_id', 'users.id as user_id')
            ->get()
            ->groupBy('module_id')
            ->map(fn($rows) => $rows->pluck('user_id')->unique()->values());

        // 1 seule requête pour les comptages de groupes par module (remplace N requêtes)
        $groupesCountByModule = DB::table('group_module')
            ->join('groups', 'groups.id', '=', 'group_module.group_id')
            ->whereIn('group_module.module_id', $moduleIds)
            ->whereIn('groups.id', $accessibleGroupIds->all())
            ->select('group_module.module_id', DB::raw('COUNT(*) as count'))
            ->groupBy('group_module.module_id')
            ->get()
            ->pluck('count', 'module_id');

        $allLearnerIds = $stagiaireIdsByModule->flatten()->unique()->values()->all();
        $allLectureIds = $lectureIdsByModule->flatten()->unique()->values()->all();
        $allSnapshots = $this->learningAnalytics->collectSnapshots($allLearnerIds, $allLectureIds);

        // 1 seule requête pour toutes les questions échouées (remplace N requêtes)
        $topFailedByModule = collect();
        if ($allLearnerIds !== []) {
            $topFailedByModule = DB::table('quiz_attempt_questions as qaq')
                ->join('quiz_attempts as qa', 'qa.id', '=', 'qaq.attempt_id')
                ->join('module_lectures as ml', 'ml.id', '=', 'qa.lecture_id')
                ->join('module_sections as ms', 'ms.id', '=', 'ml.section_id')
                ->join('quiz_questions as qq', 'qq.id', '=', 'qaq.question_id')
                ->whereIn('ms.module_id', $moduleIds)
                ->whereIn('qa.user_id', $allLearnerIds)
                ->select(
                    'ms.module_id',
                    'qq.question_text',
                    DB::raw('COUNT(*) as total_attempts'),
                    DB::raw('SUM(CASE WHEN qaq.is_correct = 0 THEN 1 ELSE 0 END) as failures')
                )
                ->groupBy('ms.module_id', 'qq.id', 'qq.question_text')
                ->having('failures', '>', 0)
                ->get()
                ->groupBy('module_id')
                ->map(function ($questions) {
                    return $questions
                        ->sortByDesc('failures')
                        ->take(3)
                        ->map(function ($q) {
                            $q->fail_rate = $q->total_attempts > 0
                                ? round(($q->failures / $q->total_attempts) * 100)
                                : 0;
                            return $q;
                        })
                        ->values();
                });
        }

        $modules = $modules->map(function ($m) use ($allSnapshots, $lectureIdsByModule, $stagiaireIdsByModule, $groupesCountByModule, $topFailedByModule) {
            $stagiaireIds = $stagiaireIdsByModule->get($m->id, collect());
            $moduleLectureIds = collect($lectureIdsByModule->get($m->id, collect()))->values()->all();
            $scopeSnapshots = $this->filterSnapshots($allSnapshots, $stagiaireIds->all(), $moduleLectureIds);
            $scopeMetrics = $this->learningAnalytics->aggregateScopeMetrics($scopeSnapshots);

            $m->stagiaires_count = $stagiaireIds->count();
            $m->groupes_count = (int) ($groupesCountByModule->get($m->id, 0));
            $m->avg_score = (int) ($scopeMetrics['average_score'] ?? 0);
            $m->started_count = (int) ($scopeMetrics['started_users_count'] ?? 0);
            $m->start_rate = $m->stagiaires_count > 0
                ? round(($m->started_count / $m->stagiaires_count) * 100)
                : 0;
            $m->top_failed = $topFailedByModule->get($m->id, collect());

            return $m;
        });

        return view('formateur.progressions.modules', [
            'modules' => $modules,
        ]);
    }
}
