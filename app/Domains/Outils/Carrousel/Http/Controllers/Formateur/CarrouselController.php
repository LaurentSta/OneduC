<?php

namespace App\Domains\Outils\Carrousel\Http\Controllers\Formateur;

use App\Domains\Outils\Carrousel\Support\AccesCarrousel;
use App\Domains\Outils\Carrousel\Support\DepotCarrousel;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CarrouselController extends Controller
{
    public function __construct(
        private readonly DepotCarrousel $depot,
        private readonly AccesCarrousel $acces,
    ) {}

    public function index(): View
    {
        $formateurId = (int) auth()->id();

        return view('outil-carrousel::formateur.index', [
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
            'title' => trim((string) ($donnees['title'] ?? '')) ?: 'Carrousel',
        ]);

        return redirect()->route('formateur.carrousel.show', $sessionId);
    }

    public function show(int $sessionId): View
    {
        $session = $this->sessionAutorisee($sessionId);

        return view('outil-carrousel::formateur.show', [
            'session' => $session,
            'joinUrl' => route('carrousel.join.code', $session->access_code),
            'slides' => $this->depot->slides($sessionId),
        ]);
    }

    public function toggle(int $sessionId): RedirectResponse
    {
        $this->sessionAutorisee($sessionId);
        $this->depot->basculer($sessionId);

        return back()->with('success', 'Le statut du carrousel a été modifié.');
    }

    public function destroy(int $sessionId): RedirectResponse
    {
        $session = $this->sessionAutorisee($sessionId);

        foreach ($this->depot->slides($sessionId) as $slide) {
            if (! empty($slide->image_path)) {
                Storage::disk('public')->delete($slide->image_path);
            }
        }

        $this->depot->supprimer($sessionId);

        return redirect()->route('formateur.carrousel.index')->with('success', 'Le carrousel a été supprimé.');
    }

    private function sessionAutorisee(int $sessionId): object
    {
        $session = $this->depot->trouver($sessionId);
        abort_unless($session, 404);
        $this->acces->verifierFormateur($session->group_id, (int) auth()->id());

        return $session;
    }
}
