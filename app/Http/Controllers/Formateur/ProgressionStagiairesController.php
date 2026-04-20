<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Formateur\Concerns\ProgressionHelpers;
use App\Models\Group;
use App\Models\User;
use App\Services\LearningAnalyticsService;
use Illuminate\Http\Request;

class ProgressionStagiairesController extends Controller
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
        $groupId = (int) $request->query('group_id', 0);
        $search = trim((string) $request->query('search', ''));

        $groupesList = Group::query()
            ->whereIn('id', $accessibleGroupIds->all())
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($groupId > 0 && ! $groupesList->pluck('id')->contains($groupId)) {
            abort(403);
        }

        $query = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($q) use ($accessibleGroupIds, $formateurId) {
                $q->where('formateur_id', $formateurId)
                  ->orWhereHas('groupesStagiaire', function ($gq) use ($accessibleGroupIds) {
                      $gq->whereIn('groups.id', $accessibleGroupIds->all());
                  });
            });

        if ($groupId > 0) {
            $query->whereHas('groupesStagiaire', function ($gq) use ($accessibleGroupIds, $groupId) {
                $gq->where('groups.id', $groupId)
                   ->whereIn('groups.id', $accessibleGroupIds->all());
            });
        }

        if ($search !== '') {
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
            ->paginate(15)
            ->withQueryString();

        $ids = $stagiaires->getCollection()->pluck('id')->all();
        $groupLectureIdsByGroup = $this->resolveGroupLectureIds($groupesList->pluck('id')->all());
        $allLectureIds = $groupLectureIdsByGroup->flatten()->unique()->values()->all();
        $lectureScopeIds = $groupId > 0
            ? collect($groupLectureIdsByGroup->get($groupId, collect()))->values()->all()
            : $allLectureIds;
        $scopeSnapshots = $this->filterSnapshots(
            $this->learningAnalytics->collectSnapshots($ids, $lectureScopeIds),
            $ids,
            $lectureScopeIds,
        );
        $userMetrics = $this->learningAnalytics->aggregateUserMetrics($scopeSnapshots);

        $stagiaires->getCollection()->transform(function ($stagiaire) use ($userMetrics) {
            $metrics = $userMetrics->get($stagiaire->id, [
                'completed_count' => 0,
                'success_rate' => 0,
                'last_activity_at' => null,
            ]);

            $stagiaire->lecons_terminees_count = (int) ($metrics['completed_count'] ?? 0);
            $stagiaire->last_completed_at = $metrics['last_activity_at'] ?? null;
            $stagiaire->taux_reussite = (int) ($metrics['success_rate'] ?? 0);

            return $stagiaire;
        });

        return view('formateur.progressions.stagiaires', [
            'stagiaires'      => $stagiaires,
            'groupes'         => $groupesList,
            'groupId'         => $groupId,
            'search'          => $search,
            'totalGroupes'    => $groupesList->count(),
            'totalStagiaires' => $stagiaires->total(),
        ]);
    }
}
