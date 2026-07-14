<?php

namespace App\Domains\Outils\Carrousel\Http\Controllers\Stagiaire;

use App\Domains\Outils\Carrousel\Support\AccesCarrousel;
use App\Domains\Outils\Carrousel\Support\DepotCarrousel;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParticipationCarrouselController extends Controller
{
    public function __construct(
        private readonly DepotCarrousel $depot,
        private readonly AccesCarrousel $acces,
    ) {}

    public function home(): View
    {
        return view('outil-carrousel::stagiaire.join');
    }

    public function resolveCode(Request $request): RedirectResponse
    {
        $donnees = $request->validate(['code' => ['required', 'string', 'max:12']]);
        $session = $this->depot->trouverParCode((string) $donnees['code']);

        if (! $session) {
            return back()->withErrors(['code' => 'Code incorrect. Vérifiez le code transmis par votre formateur.'], 'carrouselJoin')->withInput();
        }

        if (! $this->acces->peutVoir($session->group_id, (int) auth()->id())) {
            return back()->withErrors(['code' => 'Vous ne faites pas partie du groupe concerné.'], 'carrouselJoin')->withInput();
        }

        return redirect()->route('carrousel.join.code', $session->access_code);
    }

    public function joinByCode(string $code): View|RedirectResponse
    {
        $session = $this->depot->trouverParCode($code);
        if (! $session) {
            return redirect()->route('carrousel.join')
                ->withErrors(['code' => 'Code incorrect. Vérifiez le code transmis par votre formateur.'], 'carrouselJoin');
        }

        $this->acces->verifierVue($session->group_id, (int) auth()->id());

        return view('outil-carrousel::stagiaire.show', [
            'session' => $session,
            'slides' => $this->depot->slides($session->id),
        ]);
    }
}
