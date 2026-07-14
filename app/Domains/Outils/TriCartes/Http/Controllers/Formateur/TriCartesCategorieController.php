<?php

namespace App\Domains\Outils\TriCartes\Http\Controllers\Formateur;

use App\Domains\Outils\TriCartes\Support\AccesTriCartes;
use App\Domains\Outils\TriCartes\Support\DepotTriCartes;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TriCartesCategorieController extends Controller
{
    public function __construct(
        private readonly DepotTriCartes $depot,
        private readonly AccesTriCartes $acces,
    ) {}

    public function store(Request $request, int $sessionId): RedirectResponse
    {
        $session = $this->sessionAutorisee($sessionId);

        $donnees = $request->validate(['label' => ['required', 'string', 'max:120']]);
        $this->depot->ajouterCategorie($session->id, trim((string) $donnees['label']));

        return back()->with('success', 'Catégorie ajoutée.');
    }

    public function update(Request $request, int $sessionId, int $categorieId): RedirectResponse
    {
        $session = $this->sessionAutorisee($sessionId);
        $this->categorieAutorisee($session->id, $categorieId);

        $donnees = $request->validate(['label' => ['required', 'string', 'max:120']]);
        $this->depot->modifierCategorie($categorieId, trim((string) $donnees['label']));

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(int $sessionId, int $categorieId): RedirectResponse
    {
        $session = $this->sessionAutorisee($sessionId);
        $this->categorieAutorisee($session->id, $categorieId);

        $this->depot->supprimerCategorie($categorieId);

        return back()->with('success', 'Catégorie supprimée (les cartes associées ont été supprimées avec elle).');
    }

    private function sessionAutorisee(int $sessionId): object
    {
        $session = $this->depot->trouver($sessionId);
        abort_unless($session, 404);
        $this->acces->verifierFormateur($session->group_id, (int) auth()->id());

        return $session;
    }

    private function categorieAutorisee(int $sessionId, int $categorieId): object
    {
        $categorie = $this->depot->trouverCategorie($categorieId);
        abort_unless($categorie && (int) $categorie->card_sort_session_id === $sessionId, 404);

        return $categorie;
    }
}
