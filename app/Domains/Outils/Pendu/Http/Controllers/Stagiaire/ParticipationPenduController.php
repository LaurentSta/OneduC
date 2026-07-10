<?php

namespace App\Domains\Outils\Pendu\Http\Controllers\Stagiaire;

use App\Domains\Outils\Pendu\Support\AccesPendu;
use App\Domains\Outils\Pendu\Support\DepotPendu;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParticipationPenduController extends Controller
{
    public function __construct(
        private readonly DepotPendu $depot,
        private readonly AccesPendu $acces,
    ) {}

    public function home(): View
    {
        return view('outil-pendu::stagiaire.join');
    }

    public function resolveCode(Request $request): RedirectResponse
    {
        $donnees = $request->validate(['code' => ['required', 'string', 'max:12']]);
        $session = $this->depot->trouverParCode((string) $donnees['code']);

        if (! $session) {
            return back()->withErrors(['code' => 'Code incorrect. Vérifiez le code transmis par votre formateur.'], 'penduJoin')->withInput();
        }

        if (! $this->acces->peutVoir($session->group_id, (int) auth()->id())) {
            return back()->withErrors(['code' => 'Vous ne faites pas partie du groupe concerné.'], 'penduJoin')->withInput();
        }

        return redirect()->route('pendu.join.code', $session->access_code);
    }

    public function joinByCode(string $code): View|RedirectResponse
    {
        $session = $this->depot->trouverParCode($code);
        if (! $session) {
            return redirect()->route('pendu.join')
                ->withErrors(['code' => 'Code incorrect. Vérifiez le code transmis par votre formateur.'], 'penduJoin');
        }

        $this->acces->verifierVue($session->group_id, (int) auth()->id());

        return view('outil-pendu::stagiaire.show', [
            'session' => $session,
            'state' => $this->depot->etat($session),
            'peutJouer' => $this->acces->estStagiaireDuGroupe($session->group_id, (int) auth()->id()),
        ]);
    }

    public function submit(Request $request, string $code): RedirectResponse
    {
        $session = $this->depot->trouverParCode($code);
        abort_unless($session, 404);
        $this->acces->verifierStagiaire($session->group_id, (int) auth()->id());

        $donnees = $request->validate(['letter' => ['required', 'string', 'max:1']]);
        $resultat = $this->depot->proposerLettre($session->id, (int) auth()->id(), (string) $donnees['letter']);

        return $resultat['ok']
            ? back()
            : back()->withErrors(['letter' => $resultat['message']]);
    }

    public function data(string $code): JsonResponse
    {
        $session = $this->depot->trouverParCode($code);
        abort_unless($session, 404);
        $this->acces->verifierVue($session->group_id, (int) auth()->id());

        return response()->json($this->depot->etat($session));
    }
}
