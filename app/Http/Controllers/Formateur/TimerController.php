<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupTimer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimerController extends Controller
{
    public function show(Group $group): View
    {
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->whereKey($group->id)
            ->firstOrFail();

        $timer = GroupTimer::ensureForGroup($group, auth()->user());
        $timer->resolveFinished();
        $timer->refresh();

        return view('formateur.timer.show', [
            'group' => $group,
            'timer' => $timer,
            'stagiaire_url' => route('stagiaire.timer.show', ['group' => $group->id]),
            'routes' => [
                'status' => route('formateur.groupes.timer.status', $group),
                'configure' => route('formateur.groupes.timer.configure', $group),
                'start' => route('formateur.groupes.timer.start', $group),
                'pause' => route('formateur.groupes.timer.pause', $group),
                'reset' => route('formateur.groupes.timer.reset', $group),
            ],
        ]);
    }

    public function status(Group $group): JsonResponse
    {
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->whereKey($group->id)
            ->firstOrFail();

        $timer = GroupTimer::ensureForGroup($group, auth()->user());

        return response()->json($timer->toStatusArray());
    }

    public function configure(Request $request, Group $group): JsonResponse
    {
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->whereKey($group->id)
            ->firstOrFail();

        $validated = $request->validate([
            'duration_seconds' => ['required', 'integer', 'min:30', 'max:7200'],
            'label' => ['nullable', 'string', 'max:100'],
        ]);

        $timer = GroupTimer::ensureForGroup($group, auth()->user());
        $timer->configure($validated['duration_seconds'], $validated['label'] ?? null, auth()->user());

        return response()->json($timer->toStatusArray());
    }

    public function start(Group $group): JsonResponse
    {
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->whereKey($group->id)
            ->firstOrFail();

        $timer = GroupTimer::ensureForGroup($group, auth()->user());
        $timer->start(auth()->user());

        return response()->json($timer->toStatusArray());
    }

    public function pause(Group $group): JsonResponse
    {
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->whereKey($group->id)
            ->firstOrFail();

        $timer = GroupTimer::ensureForGroup($group, auth()->user());
        $timer->pause(auth()->user());

        return response()->json($timer->toStatusArray());
    }

    public function reset(Group $group): JsonResponse
    {
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->whereKey($group->id)
            ->firstOrFail();

        $timer = GroupTimer::ensureForGroup($group, auth()->user());
        $timer->reset(auth()->user());

        return response()->json($timer->toStatusArray());
    }
}
