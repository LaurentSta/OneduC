<?php

namespace App\Domains\Outils\CartesRetourner\Http\Controllers\Formateur;

use App\Domains\Outils\CartesRetourner\Support\AccesCartesRetourner;
use App\Domains\Outils\CartesRetourner\Support\DepotCartesRetourner;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CartesRetournerController extends Controller
{
    public function __construct(
        private readonly DepotCartesRetourner $depot,
        private readonly AccesCartesRetourner $acces,
    ) {}

    public function index(): View
    {
        $formateurId = (int) auth()->id();

        return view('outil-cartes-retourner::formateur.index', [
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
            'title' => trim((string) ($donnees['title'] ?? '')) ?: 'Cartes à retourner',
        ]);

        return redirect()->route('formateur.cartes-retourner.show', $sessionId);
    }

    public function show(int $sessionId): View
    {
        $session = $this->sessionAutorisee($sessionId);

        return view('outil-cartes-retourner::formateur.show', [
            'session' => $session,
            'joinUrl' => route('cartes-retourner.join.code', $session->access_code),
            'cartes' => $this->depot->cartes($sessionId),
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
            foreach ([$carte->recto_image_path, $carte->verso_image_path] as $chemin) {
                if (! empty($chemin)) {
                    Storage::disk('public')->delete($chemin);
                }
            }
        }

        $this->depot->supprimer($sessionId);

        return redirect()->route('formateur.cartes-retourner.index')->with('success', 'L\'activité a été supprimée.');
    }

    private function sessionAutorisee(int $sessionId): object
    {
        $session = $this->depot->trouver($sessionId);
        abort_unless($session, 404);
        $this->acces->verifierFormateur($session->group_id, (int) auth()->id());

        return $session;
    }
}
