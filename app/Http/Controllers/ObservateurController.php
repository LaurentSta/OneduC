<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\ScormScore;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ObservateurController extends Controller
{
    public function dashboard(): View
    {
        $user = auth()->user();
        $observedGroups = $user->groupesObserve()
            ->withCount([
                'students as stagiaires_count' => function ($query) {
                    $query->where('users.role', 'stagiaire');
                },
                'modules as modules_count',
            ])
            ->with('instructor:id,prenom,name')
            ->orderBy('groups.name')
            ->get(['groups.id', 'groups.name', 'groups.description', 'groups.instructor_id']);

        $groupIds = $observedGroups->pluck('id');

        $distinctFormateurs = $observedGroups
            ->pluck('instructor_id')
            ->filter()
            ->unique()
            ->count();

        $learnerCount = User::query()
            ->where('role', 'stagiaire')
            ->whereHas('groupesStagiaire', function ($query) use ($groupIds) {
                $query->whereIn('groups.id', $groupIds);
            })
            ->distinct('users.id')
            ->count('users.id');

        $avgScore = 0;

        if ($groupIds->isNotEmpty()) {
            $avgScore = ScormScore::query()
                ->whereHas('lecture.module.groups', function ($query) use ($groupIds) {
                    $query->whereIn('groups.id', $groupIds);
                })
                ->whereHas('user', function ($query) {
                    $query->where('role', 'stagiaire');
                })
                ->avg('last_score') ?? 0;
        }

        $latestActivityByGroup = collect();

        if ($groupIds->isNotEmpty()) {
            $latestActivityByGroup = DB::table('progressions')
                ->join('group_user', 'group_user.user_id', '=', 'progressions.user_id')
                ->whereIn('group_user.group_id', $groupIds)
                ->selectRaw('group_user.group_id, MAX(progressions.completed_at) as last_completed_at')
                ->groupBy('group_user.group_id')
                ->pluck('last_completed_at', 'group_user.group_id');
        }

        $observedGroups = $observedGroups->map(function (Group $group) use ($latestActivityByGroup) {
            $group->last_completed_at = $latestActivityByGroup[$group->id] ?? null;

            return $group;
        });

        return view('observateur.index', [
            'profileData' => Auth::user(),
            'groupCount' => $observedGroups->count(),
            'formateurCount' => $distinctFormateurs,
            'learnerCount' => $learnerCount,
            'avgScoreRounded' => (int) round($avgScore),
            'observedGroups' => $observedGroups,
        ]);
    }
}
