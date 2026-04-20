<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Formateur\Concerns\ProgressionHelpers;
use App\Models\Group;
use App\Services\LearningAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgressionGroupesController extends Controller
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
        $search = trim((string) $request->query('search', ''));

        $groupesList = Group::query()
            ->whereIn('id', $accessibleGroupIds->all())
            ->orderBy('name')
            ->get(['id', 'name']);

        $groupes = Group::query()
            ->whereIn('id', $accessibleGroupIds->all())
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15, ['id', 'name'])
            ->withQueryString();

        $pageGroupIds = $groupes->getCollection()->pluck('id')->all();
        $fifteenDaysAgo = now()->subDays(15);
        $learnerIdsByGroup = $this->resolveGroupLearnerIds($pageGroupIds);
        $lectureIdsByGroup = $this->resolveGroupLectureIds($pageGroupIds);
        $allLearnerIds = $learnerIdsByGroup->flatten()->unique()->values()->all();
        $allLectureIds = $lectureIdsByGroup->flatten()->unique()->values()->all();
        $snapshots = $this->learningAnalytics->collectSnapshots($allLearnerIds, $allLectureIds);

        $groupes->getCollection()->transform(function ($group) use ($fifteenDaysAgo, $learnerIdsByGroup, $lectureIdsByGroup, $snapshots) {
            $stagiaireIds = collect($learnerIdsByGroup->get($group->id, collect()))->values();
            $groupLectureIds = collect($lectureIdsByGroup->get($group->id, collect()))->values()->all();
            $scopeSnapshots = $this->filterSnapshots($snapshots, $stagiaireIds->all(), $groupLectureIds);
            $scopeMetrics = $this->learningAnalytics->aggregateScopeMetrics($scopeSnapshots, $fifteenDaysAgo);

            $startedCount = (int) ($scopeMetrics['started_users_count'] ?? 0);
            $recentCount = (int) ($scopeMetrics['recent_users_count'] ?? 0);

            $group->stagiaires_count = $stagiaireIds->count();
            $group->modules_count = (int) DB::table('group_module')->where('group_id', $group->id)->count();
            $group->total_site_time = (int) \App\Models\User::whereIn('id', $stagiaireIds)->sum('total_site_time');
            $group->lecons_terminees_count = (int) ($scopeMetrics['completed_count'] ?? 0);
            $group->taux_reussite = (int) ($scopeMetrics['success_rate'] ?? 0);
            $group->not_started_count = max(0, $stagiaireIds->count() - $startedCount);
            $group->inactive_count = max(0, $startedCount - $recentCount);

            return $group;
        });

        return view('formateur.progressions.groupes', [
            'groupes'      => $groupes,
            'groupesList'  => $groupesList,
            'totalGroupes' => $groupes->total(),
            'search'       => $search,
        ]);
    }
}
