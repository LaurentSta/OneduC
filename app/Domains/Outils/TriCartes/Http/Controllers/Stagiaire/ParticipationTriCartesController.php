<?php

namespace App\Domains\Outils\TriCartes\Http\Controllers\Stagiaire;

use App\Domains\Outils\TriCartes\Support\AccesTriCartes;
use App\Domains\Outils\TriCartes\Support\DepotTriCartes;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParticipationTriCartesController extends Controller
{
    public function __construct(
        private readonly DepotTriCartes $depot,
        private readonly AccesTriCartes $acces,
    ) {}

    public function home(): View
    {
        return view('outil-tri-cartes::stagiaire.join');
    }

    public function resolveCode(Request $request): RedirectResponse
    {
        $donnees = $request->validate(['code' => ['required', 'string', 'max:12']]);
        $session = $this->depot->trouverParCode((string) $donnees['code']);

        if (! $session) {
            return back()->withErrors(['code' => 'Code incorrect. Vérifiez le code transmis par votre formateur.'], 'triCartesJoin')->withInput();
        }

        if (! $this->acces->peutVoir($session->group_id, (int) auth()->id())) {
            return back()->withErrors(['code' => 'Vous ne faites pas partie du groupe concerné.'], 'triCartesJoin')->withInput();
        }

        return redirect()->route('tri-cartes.join.code', $session->access_code);
    }

    public function joinByCode(string $code): View|RedirectResponse
    {
        $session = $this->depot->trouverParCode($code);
        if (! $session) {
            return redirect()->route('tri-cartes.join')
                ->withErrors(['code' => 'Code incorrect. Vérifiez le code transmis par votre formateur.'], 'triCartesJoin');
        }

        $this->acces->verifierVue($session->group_id, (int) auth()->id());

        return view('outil-tri-cartes::stagiaire.show', [
            'session' => $session,
            'categories' => $this->depot->categories($session->id),
            'cartes' => $this->depot->cartes($session->id),
            'tentative' => $this->depot->tentativePourStagiaire($session->id, (int) auth()->id()),
        ]);
    }

    public function submit(Request $request, string $code): JsonResponse
    {
        $session = $this->depot->trouverParCode($code);
        abort_unless($session, 404);
        $this->acces->verifierStagiaire($session->group_id, (int) auth()->id());
        abort_unless($session->is_active, 422, "Cette activité n'est plus ouverte.");

        $donnees = $request->validate([
            'placements' => ['required', 'array'],
            'placements.*' => ['required', 'integer'],
        ]);

        $resultat = $this->depot->soumettre($session->id, (int) auth()->id(), $donnees['placements']);

        return response()->json($resultat);
    }
}
