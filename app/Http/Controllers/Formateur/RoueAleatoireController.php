<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\RandomWheelSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoueAleatoireController extends Controller
{
    public function index(): View
    {
        $formateurId = (int) auth()->id();

        $groups = Group::query()
            ->accessibleByTrainer($formateurId)
            ->withCount('students')
            ->orderBy('name')
            ->get(['id', 'name']);

        $sessions = RandomWheelSession::where('formateur_id', $formateurId)
            ->with('group')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('formateur.outils.roue_index', compact('groups', 'sessions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
        ]);

        $formateurId = (int) auth()->id();

        $group = Group::query()
            ->accessibleByTrainer($formateurId)
            ->findOrFail((int) $data['group_id']);

        $entries = $group->students()
            ->get(['users.id', 'prenom', 'name'])
            ->map(fn ($u) => [
                'id'   => (int) $u->id,
                'name' => trim($u->prenom . ' ' . mb_substr($u->name, 0, 1) . '.'),
            ])
            ->values()
            ->all();

        abort_if(empty($entries), 422, 'Ce groupe ne contient aucun stagiaire.');

        $session = RandomWheelSession::create([
            'formateur_id'   => $formateurId,
            'group_id'       => $group->id,
            'access_code'    => $this->generateCode(),
            'entries'        => $entries,
            'picks'          => [],
            'current_pick_id'=> null,
        ]);

        return redirect()->route('formateur.roue.show', $session);
    }

    public function show(RandomWheelSession $session): View
    {
        abort_unless($session->formateur_id === (int) auth()->id(), 403);
        $session->load('group');
        $joinUrl = route('roue.join', $session->access_code);

        return view('formateur.outils.roue_show', compact('session', 'joinUrl'));
    }

    public function spin(RandomWheelSession $session): JsonResponse
    {
        abort_unless($session->formateur_id === (int) auth()->id(), 403);

        $available = $session->availableEntries();

        if (empty($available)) {
            return response()->json(['error' => 'Tous les participants ont été tirés.'], 422);
        }

        $winner = $available[array_rand($available)];

        $picks = $session->picks ?? [];
        $picks[] = $winner['id'];

        $session->update([
            'current_pick_id' => $winner['id'],
            'picks'           => $picks,
            'spun_at'         => now(),
        ]);

        $winnerIndex = collect($session->entries)->search(fn ($e) => $e['id'] === $winner['id']);

        return response()->json([
            'winner'       => $winner,
            'winner_index' => $winnerIndex,
            'picks_count'  => count($picks),
            'total'        => count($session->entries),
            'is_exhausted' => $session->isExhausted(),
            'state_key'    => $session->stateKey(),
        ]);
    }

    public function reset(RandomWheelSession $session): RedirectResponse
    {
        abort_unless($session->formateur_id === (int) auth()->id(), 403);

        $session->update([
            'picks'           => [],
            'current_pick_id' => null,
            'spun_at'         => null,
        ]);

        return redirect()->route('formateur.roue.show', $session);
    }

    public function state(RandomWheelSession $session): JsonResponse
    {
        abort_unless($session->formateur_id === (int) auth()->id(), 403);

        return response()->json($this->buildState($session));
    }

    public static function buildState(RandomWheelSession $session): array
    {
        return [
            'current_pick'  => $session->currentPick(),
            'current_index' => $session->current_pick_id
                ? collect($session->entries)->search(fn ($e) => $e['id'] === (int) $session->current_pick_id)
                : null,
            'picks'         => $session->picks ?? [],
            'picks_count'   => count($session->picks ?? []),
            'total'         => count($session->entries),
            'is_exhausted'  => $session->isExhausted(),
            'state_key'     => $session->stateKey(),
        ];
    }

    private function generateCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (RandomWheelSession::where('access_code', $code)->exists());

        return $code;
    }
}
