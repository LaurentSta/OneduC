<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Formateur\RoueAleatoireController;
use App\Models\RandomWheelSession;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class RoueAleatoireParticipationController extends Controller
{
    public function show(string $code): View
    {
        $session = RandomWheelSession::where('access_code', strtoupper($code))
            ->with('group')
            ->firstOrFail();

        $this->ensureCanParticipate($session);

        RoueAleatoireController::syncEntriesFromGroup($session);

        $stateUrl = route('roue.state', $code);

        return view('roue.show', compact('session', 'stateUrl'));
    }

    public function state(string $code): JsonResponse
    {
        $session = RandomWheelSession::where('access_code', strtoupper($code))
            ->with('group')
            ->firstOrFail();

        $this->ensureCanParticipate($session);

        RoueAleatoireController::syncEntriesFromGroup($session);

        return response()->json(RoueAleatoireController::buildState($session));
    }

    private function ensureCanParticipate(RandomWheelSession $session): void
    {
        /** @var \App\Models\Group|null $group */
        $group = $session->group;

        abort_unless($group && (bool) $group->is_active, 404);

        $isMember = $group->students()
            ->where('users.id', auth()->id())
            ->exists();

        abort_unless($isMember, 404);
    }
}
