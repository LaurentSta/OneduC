<?php

namespace App\Domains\Outils\TriCartes\Http\Controllers\Formateur;

use App\Domains\Outils\TriCartes\Support\AccesTriCartes;
use App\Domains\Outils\TriCartes\Support\DepotTriCartes;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TriCartesController extends Controller
{
    public function __construct(
        private readonly DepotTriCartes $depot,
        private readonly AccesTriCartes $acces,
    ) {}

    public function index(): View
    {
        $formateurId = (int) auth()->id();

        return view('outil-tri-cartes::formateur.index', [
            'groups' => $this->depot->groupesPourFormateur($formateurId),
            'sessions' => $this->depot->sessionsPourFormateur($formateurId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $formateurId = (int) auth()->id();
        $groupId = (int) $donnees['group_id'];
        $this->acces->verifierFormateur($groupId, $formateurId);

        $sessionId = $this->depot->creer([
            'formateur_id' => $formateurId,
            'group_id' => $groupId,
            'title' => trim((string) ($donnees['title'] ?? '')) ?: 'Cartes à trier',
        ]);

        return redirect()->route('formateur.tri-cartes.show', $sessionId);
    }

    public function show(int $sessionId): View
    {
        $session = $this->sessionAutorisee($sessionId);

        return view('outil-tri-cartes::formateur.show', [
            'session' => $session,
            'joinUrl' => route('tri-cartes.join.code', $session->access_code),
            'categories' => $this->depot->categories($sessionId),
            'cartes' => $this->depot->cartes($sessionId),
            'resultats' => $this->depot->resultatsPourFormateur($sessionId),
        ]);
    }

    public function toggle(int $sessionId): RedirectResponse
    {
        $this->sessionAutorisee($sessionId);
        $this->depot->basculer($sessionId);

        return back()->with('success', 'Le statut a été modifié.');
    }

    public function destroy(int $sessionId): RedirectResponse
    {
        $session = $this->sessionAutorisee($sessionId);

        foreach ($this->depot->cartes($sessionId) as $carte) {
            if (! empty($carte->image_path)) {
                Storage::disk('public')->delete($carte->image_path);
            }
        }

        $this->depot->supprimer($sessionId);

        return redirect()->route('formateur.tri-cartes.index')->with('success', 'L\'activité a été supprimée.');
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
