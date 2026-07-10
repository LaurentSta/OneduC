<?php

namespace App\Domains\Outils\Memoire\Http\Controllers\Formateur;

use App\Domains\Outils\Memoire\Support\AccesMemoire;
use App\Domains\Outils\Memoire\Support\DepotMemoire;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemoireController extends Controller
{
    public function __construct(
        private readonly DepotMemoire $depot,
        private readonly AccesMemoire $acces,
    ) {}

    public function index(): View
    {
        $formateurId = (int) auth()->id();

        return view('outil-memoire::formateur.index', [
            'groups' => $this->depot->groupesPourFormateur($formateurId),
            'sessions' => $this->depot->sessionsPourFormateur($formateurId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'pairs' => ['required', 'array', 'min:3', 'max:10'],
            'pairs.*.a' => ['required', 'string', 'max:100'],
            'pairs.*.b' => ['required', 'string', 'max:100'],
        ]);

        $paires = collect($donnees['pairs'])
            ->map(fn (array $paire): array => [
                'a' => trim((string) $paire['a']),
                'b' => trim((string) $paire['b']),
            ])
            ->filter(fn (array $paire): bool => $paire['a'] !== '' && $paire['b'] !== '')
            ->values();

        abort_if($paires->count() < 3, 422, 'Le jeu doit contenir au moins trois paires valides.');

        $formateurId = (int) auth()->id();
        $groupId = (int) $donnees['group_id'];
        $this->acces->verifierFormateur($groupId, $formateurId);

        $sessionId = $this->depot->creer([
            'formateur_id' => $formateurId,
            'group_id' => $groupId,
            'title' => trim((string) ($donnees['title'] ?? '')) ?: 'Jeu de mémoire',
            'pairs' => $paires->all(),
        ]);

        return redirect()->route('formateur.memoire.show', $sessionId);
    }

    public function show(int $sessionId): View
    {
        $session = $this->sessionAutorisee($sessionId);

        return view('outil-memoire::formateur.show', [
            'session' => $session,
            'joinUrl' => route('memoire.join.code', $session->access_code),
            'state' => $this->depot->etat($sessionId),
        ]);
    }

    public function toggle(int $sessionId): RedirectResponse
    {
        $this->sessionAutorisee($sessionId);
        $this->depot->basculer($sessionId);

        return back()->with('success', 'Le statut de la partie a été modifié.');
    }

    public function destroy(int $sessionId): RedirectResponse
    {
        $this->sessionAutorisee($sessionId);
        $this->depot->supprimer($sessionId);

        return redirect()->route('formateur.memoire.index')->with('success', 'La partie a été supprimée.');
    }

    public function state(int $sessionId): JsonResponse
    {
        $this->sessionAutorisee($sessionId);

        return response()->json($this->depot->etat($sessionId));
    }

    private function sessionAutorisee(int $sessionId): object
    {
        $session = $this->depot->trouver($sessionId);
        abort_unless($session, 404);
        $this->acces->verifierFormateur($session->group_id, (int) auth()->id());

        return $session;
    }
}
