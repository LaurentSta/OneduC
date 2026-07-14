<?php

namespace App\Domains\Outils\CartesRetourner\Http\Controllers\Stagiaire;

use App\Domains\Outils\CartesRetourner\Support\AccesCartesRetourner;
use App\Domains\Outils\CartesRetourner\Support\DepotCartesRetourner;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParticipationCartesRetournerController extends Controller
{
    public function __construct(
        private readonly DepotCartesRetourner $depot,
        private readonly AccesCartesRetourner $acces,
    ) {}

    public function home(): View
    {
        return view('outil-cartes-retourner::stagiaire.join');
    }

    public function resolveCode(Request $request): RedirectResponse
    {
        $donnees = $request->validate(['code' => ['required', 'string', 'max:12']]);
        $session = $this->depot->trouverParCode((string) $donnees['code']);

        if (! $session) {
            return back()->withErrors(['code' => 'Code incorrect. Vérifiez le code transmis par votre formateur.'], 'cartesRetournerJoin')->withInput();
        }

        if (! $this->acces->peutVoir($session->group_id, (int) auth()->id())) {
            return back()->withErrors(['code' => 'Vous ne faites pas partie du groupe concerné.'], 'cartesRetournerJoin')->withInput();
        }

        return redirect()->route('cartes-retourner.join.code', $session->access_code);
    }

    public function joinByCode(string $code): View|RedirectResponse
    {
        $session = $this->depot->trouverParCode($code);
        if (! $session) {
            return redirect()->route('cartes-retourner.join')
                ->withErrors(['code' => 'Code incorrect. Vérifiez le code transmis par votre formateur.'], 'cartesRetournerJoin');
        }

        $this->acces->verifierVue($session->group_id, (int) auth()->id());

        return view('outil-cartes-retourner::stagiaire.show', [
            'session' => $session,
            'cartes' => $this->depot->cartes($session->id),
        ]);
    }
}
