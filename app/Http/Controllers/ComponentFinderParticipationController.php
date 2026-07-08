<?php

namespace App\Http\Controllers;

use App\Models\ComponentFinderAttempt;
use App\Models\ComponentFinderSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComponentFinderParticipationController extends Controller
{
    public function home(): View
    {
        return view('composant.join');
    }

    public function resolveCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:12'],
        ]);

        return redirect()->route('composants.join.code', ['code' => $this->normalizeCode($data['code'])]);
    }

    public function joinByCode(string $code): View
    {
        $session = $this->findSessionByCode($code);
        $this->ensureCanView($session, auth()->user());

        $existingAttempt = ComponentFinderAttempt::query()
            ->where('component_finder_session_id', $session->id)
            ->where('user_id', (int) auth()->id())
            ->first();

        $zones = collect($session->zones)->values()->shuffle();

        return view('composant.show', [
            'session' => $session,
            'zones' => $zones,
            'existingAttempt' => $existingAttempt,
        ]);
    }

    public function submit(Request $request, string $code): JsonResponse
    {
        $session = $this->findSessionByCode($code);
        $this->ensureCanAnswer($session, $request->user());

        abort_if(! $session->is_active, 422, 'Ce jeu est fermé pour le moment.');

        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0'],
            'total' => ['required', 'integer', 'min:1'],
            'duration_seconds' => ['required', 'integer', 'min:0'],
            'details' => ['required', 'array'],
            'details.*.label' => ['required', 'string', 'max:100'],
            'details.*.correct' => ['required', 'boolean'],
            'details.*.time_ms' => ['required', 'integer', 'min:0'],
        ]);

        $total = count($session->zones ?? []);
        abort_unless((int) $data['total'] === $total, 422, 'Le nombre de composants ne correspond pas à la session.');

        $score = collect($data['details'])
            ->filter(fn (array $detail): bool => (bool) $detail['correct'])
            ->count();

        ComponentFinderAttempt::query()->updateOrCreate(
            [
                'component_finder_session_id' => $session->id,
                'user_id' => (int) $request->user()->id,
            ],
            [
                'score' => min($score, $total),
                'total' => $total,
                'duration_seconds' => (int) $data['duration_seconds'],
                'details' => $data['details'],
            ]
        );

        return response()->json(['success' => true]);
    }

    private function findSessionByCode(string $code): ComponentFinderSession
    {
        return ComponentFinderSession::query()
            ->where('access_code', $this->normalizeCode($code))
            ->with('group')
            ->firstOrFail();
    }

    private function ensureCanView(ComponentFinderSession $session, ?User $user): void
    {
        abort_unless($user instanceof User, 403);

        $group = $session->group;
        abort_unless($group, 404);

        $isOwner = (int) $group->instructor_id === (int) $user->id;
        $isCoTrainer = $group->coFormateurs()->where('users.id', (int) $user->id)->exists();
        $isMember = $group->users()->where('users.id', (int) $user->id)->exists();

        abort_unless($isOwner || $isCoTrainer || $isMember, 403);
    }

    private function ensureCanAnswer(ComponentFinderSession $session, ?User $user): void
    {
        abort_unless($user instanceof User, 403);

        $group = $session->group;
        abort_unless($group, 404);

        abort_unless(
            $group->students()->where('users.id', (int) $user->id)->exists(),
            403
        );
    }

    private function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }
}
