<?php

namespace App\Domains\Outils\Pendu\Http\Controllers\Formateur;

use App\Domains\Outils\Pendu\Support\AccesPendu;
use App\Domains\Outils\Pendu\Support\DepotPendu;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenduController extends Controller
{
    public function __construct(
        private readonly DepotPendu $depot,
        private readonly AccesPendu $acces,
    ) {}

    public function index(): View
    {
        $formateurId = (int) auth()->id();

        return view('outil-pendu::formateur.index', [
            'groups' => $this->depot->groupesPourFormateur($formateurId),
            'sessions' => $this->depot->sessionsPourFormateur($formateurId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'word' => ['required', 'string', 'max:60', 'regex:/^[\pL \'-]+$/u'],
        ]);

        $formateurId = (int) auth()->id();
        $groupId = (int) $donnees['group_id'];
        $this->acces->verifierFormateur($groupId, $formateurId);

        $sessionId = $this->depot->creer([
            'formateur_id' => $formateurId,
            'group_id' => $groupId,
            'title' => trim((string) ($donnees['title'] ?? '')) ?: 'Jeu du pendu',
            'word' => trim((string) $donnees['word']),
        ]);

        return redirect()->route('formateur.pendu.show', $sessionId);
    }

    public function show(int $sessionId): View
    {
        $session = $this->sessionAutorisee($sessionId);

        return view('outil-pendu::formateur.show', [
            'session' => $session,
            'joinUrl' => route('pendu.join.code', $session->access_code),
            'state' => $this->depot->etat($session),
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

        return redirect()->route('formateur.pendu.index')->with('success', 'La partie a été supprimée.');
    }

    public function state(int $sessionId): JsonResponse
    {
        $session = $this->sessionAutorisee($sessionId);

        return response()->json($this->depot->etat($session));
    }

    private function sessionAutorisee(int $sessionId): object
    {
        $session = $this->depot->trouver($sessionId);
        abort_unless($session, 404);
        $this->acces->verifierFormateur($session->group_id, (int) auth()->id());

        return $session;
    }
}
