<?php

namespace App\Domains\Outils\Memoire\Http\Controllers\Stagiaire;

use App\Domains\Outils\Memoire\Support\AccesMemoire;
use App\Domains\Outils\Memoire\Support\DepotMemoire;
use App\Domains\Outils\Memoire\Support\JeuMemoire;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParticipationMemoireController extends Controller
{
    public function __construct(
        private readonly DepotMemoire $depot,
        private readonly AccesMemoire $acces,
    ) {}

    public function home(): View
    {
        return view('outil-memoire::stagiaire.join');
    }

    public function resolveCode(Request $request): RedirectResponse
    {
        $donnees = $request->validate(['code' => ['required', 'string', 'max:12']]);
        $session = $this->depot->trouverParCode((string) $donnees['code']);

        if (! $session) {
            return back()->withErrors(['code' => 'Code incorrect. Vérifiez le code transmis par votre formateur.'], 'memoireJoin')->withInput();
        }

        if (! $this->acces->peutVoir($session->group_id, (int) auth()->id())) {
            return back()->withErrors(['code' => 'Vous ne faites pas partie du groupe concerné.'], 'memoireJoin')->withInput();
        }

        return redirect()->route('memoire.join.code', $session->access_code);
    }

    public function joinByCode(string $code): View|RedirectResponse
    {
        $session = $this->depot->trouverParCode($code);
        if (! $session) {
            return redirect()->route('memoire.join')
                ->withErrors(['code' => 'Code incorrect. Vérifiez le code transmis par votre formateur.'], 'memoireJoin');
        }

        $userId = (int) auth()->id();
        $this->acces->verifierVue($session->group_id, $userId);

        return view('outil-memoire::stagiaire.show', [
            'session' => $session,
            'cards' => JeuMemoire::construireJeu($session->pairs),
            'existingAttempt' => $this->depot->tentativeUtilisateur($session->id, $userId),
            'peutJouer' => $this->acces->estStagiaireDuGroupe($session->group_id, $userId),
        ]);
    }

    public function submit(Request $request, string $code): JsonResponse
    {
        $session = $this->depot->trouverParCode($code);
        abort_unless($session, 404);
        abort_if(! $session->is_active, 422, 'Ce jeu est fermé pour le moment.');
        $this->acces->verifierStagiaire($session->group_id, (int) auth()->id());

        $donnees = $request->validate([
            'moves' => ['required', 'integer', 'min:'.count($session->pairs), 'max:10000'],
            'duration_seconds' => ['required', 'integer', 'min:1', 'max:86400'],
        ]);

        $this->depot->enregistrerTentative(
            $session->id,
            (int) auth()->id(),
            (int) $donnees['moves'],
            (int) $donnees['duration_seconds'],
        );

        return response()->json(['success' => true]);
    }
}
