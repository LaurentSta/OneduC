<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\RandomWheelSession;
use App\Models\User;
use App\Services\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        $entries = $this->buildEntriesFromGroup($group);

        abort_if(empty($entries), 422, 'Ce groupe ne contient aucun stagiaire.');

        $session = RandomWheelSession::create([
            'formateur_id' => $formateurId,
            'group_id' => $group->id,
            'access_code' => CodeGeneratorService::generateUniqueCode(RandomWheelSession::class),
            'entries' => $entries,
            'active_entry_ids' => collect($entries)->pluck('id')->all(),
            'picks' => [],
            'current_pick_id' => null,
        ]);

        return redirect()->route('formateur.roue.show', $session);
    }

    public function show(RandomWheelSession $session): View
    {
        abort_unless($session->formateur_id === (int) auth()->id(), 403);
        $session->load('group');
        $this->syncEntriesFromGroup($session);

        $joinUrl = route('roue.join', $session->access_code);
        $participantsUrl = route('formateur.roue.participants', $session);

        return view('formateur.outils.roue_show', compact('session', 'joinUrl', 'participantsUrl'));
    }

    public function spin(RandomWheelSession $session): JsonResponse
    {
        abort_unless($session->formateur_id === (int) auth()->id(), 403);
        $session->load('group');
        $this->syncEntriesFromGroup($session);

        $available = $session->availableEntries();

        if (empty($available)) {
            $errorMessage = count($session->activeEntryIds()) === 0
                ? 'Aucun stagiaire actif sur la roue.'
                : 'Tous les stagiaires actifs ont été tirés.';

            return response()->json(['error' => $errorMessage], 422);
        }

        $winner = $available[array_rand($available)];

        $picks = $session->picks ?? [];
        $picks[] = $winner['id'];

        $session->update([
            'current_pick_id' => $winner['id'],
            'picks' => $picks,
            'spun_at' => now(),
        ]);

        $winnerIndex = collect($session->activeEntries())->search(fn ($e) => $e['id'] === $winner['id']);

        return response()->json([
            'winner' => $winner,
            'winner_index' => $winnerIndex,
            'picks_count' => count($picks),
            'total' => count($session->activeEntries()),
            'total_entries' => count($session->entries ?? []),
            'is_exhausted' => $session->isExhausted(),
            'state_key' => $session->stateKey(),
        ]);
    }

    public function updateParticipants(Request $request, RandomWheelSession $session): RedirectResponse|JsonResponse
    {
        abort_unless($session->formateur_id === (int) auth()->id(), 403);
        $session->load('group');
        $this->syncEntriesFromGroup($session);

        $entryIds = collect($session->entryIds());

        $data = $request->validate([
            'active_entry_ids' => ['nullable', 'array'],
            'active_entry_ids.*' => ['integer', Rule::in($entryIds->all())],
        ]);

        $requestedActive = collect($data['active_entry_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique();

        // Keep the original wheel order to keep indexes stable in animations.
        $activeEntryIds = $entryIds
            ->filter(fn ($id) => $requestedActive->contains($id))
            ->values()
            ->all();

        $currentPickId = $session->current_pick_id ? (int) $session->current_pick_id : null;
        if ($currentPickId !== null && ! in_array($currentPickId, $activeEntryIds, true)) {
            $currentPickId = null;
        }

        $session->update([
            'active_entry_ids' => $activeEntryIds,
            'current_pick_id' => $currentPickId,
        ]);

        $session->refresh();

        if ($request->expectsJson()) {
            return response()->json(self::buildState($session));
        }

        return redirect()->route('formateur.roue.show', $session);
    }

    public function reset(RandomWheelSession $session): RedirectResponse
    {
        abort_unless($session->formateur_id === (int) auth()->id(), 403);
        $session->load('group');
        $this->syncEntriesFromGroup($session);

        $session->update([
            'picks' => [],
            'current_pick_id' => null,
            'spun_at' => null,
        ]);

        return redirect()->route('formateur.roue.show', $session);
    }

    public function state(RandomWheelSession $session): JsonResponse
    {
        abort_unless($session->formateur_id === (int) auth()->id(), 403);
        $session->load('group');
        $this->syncEntriesFromGroup($session);

        return response()->json($this->buildState($session));
    }

    public static function buildState(RandomWheelSession $session): array
    {
        $activeEntries = $session->activeEntries();
        $activeEntryIds = $session->activeEntryIds();
        $currentPick = $session->currentPick();
        if ($currentPick && ! in_array((int) $currentPick['id'], $activeEntryIds, true)) {
            $currentPick = null;
        }

        return [
            'entries' => $session->entries ?? [],
            'current_pick' => $currentPick,
            'current_index' => $currentPick
                ? collect($activeEntries)->search(fn ($e) => $e['id'] === (int) $currentPick['id'])
                : null,
            'picks' => $session->picks ?? [],
            'picks_count' => count($session->picks ?? []),
            'total' => count($activeEntries),
            'total_entries' => count($session->entries ?? []),
            'active_entry_ids' => $activeEntryIds,
            'is_exhausted' => $session->isExhausted(),
            'state_key' => $session->stateKey(),
        ];
    }

    public static function syncEntriesFromGroup(RandomWheelSession $session): void
    {
        if (! $session->group) {
            return;
        }

        $freshEntries = self::entriesFromGroup($session->group);
        $freshById = collect($freshEntries)->keyBy('id');

        $entries = collect($session->entries ?? [])
            ->filter(fn ($entry) => $freshById->has((int) ($entry['id'] ?? 0)))
            ->map(fn ($entry) => $freshById->get((int) $entry['id']))
            ->values();

        $knownIds = $entries->pluck('id')->map(fn ($id) => (int) $id);
        $newEntries = collect($freshEntries)
            ->reject(fn ($entry) => $knownIds->contains((int) $entry['id']))
            ->values();

        $entries = $entries->concat($newEntries)->values()->all();
        $entryIds = collect($entries)->pluck('id')->map(fn ($id) => (int) $id);
        $newIds = $newEntries->pluck('id')->map(fn ($id) => (int) $id);

        $currentActiveIds = $session->active_entry_ids === null
            ? collect($session->entryIds())
            : collect($session->active_entry_ids)->map(fn ($id) => (int) $id);

        $activeEntryIds = $entryIds
            ->filter(fn ($id) => $currentActiveIds->contains($id) || $newIds->contains($id))
            ->values()
            ->all();

        $picks = collect($session->picks ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $entryIds->contains($id))
            ->values()
            ->all();

        $currentPickId = $session->current_pick_id ? (int) $session->current_pick_id : null;
        if ($currentPickId !== null && ! $entryIds->contains($currentPickId)) {
            $currentPickId = null;
        }

        $changes = [
            'entries' => $entries,
            'active_entry_ids' => $activeEntryIds,
            'picks' => $picks,
            'current_pick_id' => $currentPickId,
        ];

        if (
            $entries === ($session->entries ?? [])
            && $activeEntryIds === $session->activeEntryIds()
            && $picks === ($session->picks ?? [])
            && $currentPickId === ($session->current_pick_id ? (int) $session->current_pick_id : null)
        ) {
            return;
        }

        $session->forceFill($changes)->save();
        $session->refresh()->load('group');
    }

    private function buildEntriesFromGroup(Group $group): array
    {
        return self::entriesFromGroup($group);
    }

    private static function entriesFromGroup(Group $group): array
    {
        return $group->students()
            ->orderBy('name')
            ->orderBy('prenom')
            ->get(['users.id', 'prenom', 'name', 'email'])
            ->map(fn (User $student) => [
                'id' => (int) $student->id,
                'name' => self::studentWheelName($student),
            ])
            ->values()
            ->all();
    }

    private static function studentWheelName(User $student): string
    {
        $fullName = trim(collect([$student->prenom, $student->name])
            ->filter(fn ($part) => filled($part))
            ->implode(' '));

        if ($fullName !== '') {
            return $fullName;
        }

        return $student->email ?: 'Stagiaire #'.$student->id;
    }
}
