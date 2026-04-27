<?php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupTimer;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TimerController extends Controller
{
    public function show(Group $group): View
    {
        abort_unless((bool) $group->is_active, 404);

        $isMember = $group->students()
            ->where('users.id', auth()->id())
            ->exists();

        abort_unless($isMember, 404);

        $timer = GroupTimer::ensureForGroup($group, auth()->user());
        $timer->resolveFinished();
        $timer->refresh();

        return view('stagiaire.timer.show', [
            'group' => $group,
            'timer' => $timer,
            'routes' => [
                'status' => route('stagiaire.timer.status', ['group' => $group->id]),
            ],
        ]);
    }

    public function status(Group $group): JsonResponse
    {
        abort_unless((bool) $group->is_active, 404);

        $isMember = $group->students()
            ->where('users.id', auth()->id())
            ->exists();

        abort_unless($isMember, 404);

        $timer = GroupTimer::ensureForGroup($group, auth()->user());

        return response()->json($timer->toStatusArray());
    }
}
