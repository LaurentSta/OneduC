<?php

namespace App\Domains\Outils\Minuteur\Http\Controllers\Stagiaire;

use App\Domains\Outils\Minuteur\Support\AccesMinuteur;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupTimer;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TimerController extends Controller
{
    public function __construct(private readonly AccesMinuteur $acces) {}

    public function show(Group $group): View
    {
        $this->acces->assertStagiaireAccess($group);

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
        $this->acces->assertStagiaireAccess($group);

        $timer = GroupTimer::ensureForGroup($group, auth()->user());

        return response()->json($timer->toStatusArray());
    }
}
