<?php

namespace App\Domains\Outils\TriCartes\Http\Controllers\Formateur;

use App\Domains\Outils\TriCartes\Support\AccesTriCartes;
use App\Domains\Outils\TriCartes\Support\DepotTriCartes;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TriCartesCarteController extends Controller
{
    public function __construct(
        private readonly DepotTriCartes $depot,
        private readonly AccesTriCartes $acces,
    ) {}

    public function store(Request $request, int $sessionId): RedirectResponse
    {
        $session = $this->sessionAutorisee($sessionId);

        $donnees = $request->validate([
            'text' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,avif', 'max:4096'],
            'correct_category_id' => ['required', 'integer'],
        ]);

        $categorie = $this->depot->trouverCategorie((int) $donnees['correct_category_id']);
        abort_unless($categorie && (int) $categorie->card_sort_session_id === $session->id, 422);

        $texte = trim((string) ($donnees['text'] ?? ''));
        if ($texte === '' && ! $request->hasFile('image')) {
            return back()->withErrors(['text' => 'Ajoutez au moins un texte ou une image.'])->withInput();
        }

        $this->depot->ajouterCarte($session->id, [
            'correct_category_id' => $categorie->id,
            'text' => $texte ?: null,
            'image_path' => $request->hasFile('image') ? $this->stocker($request, $session->id) : null,
        ]);

        return back()->with('success', 'Carte ajoutée.');
    }

    public function destroy(int $sessionId, int $carteId): RedirectResponse
    {
        $session = $this->sessionAutorisee($sessionId);
        $carte = $this->carteAutorisee($session->id, $carteId);

        if (! empty($carte->image_path)) {
            Storage::disk('public')->delete($carte->image_path);
        }

        $this->depot->supprimerCarte($carteId);

        return back()->with('success', 'Carte supprimée.');
    }

    public function move(Request $request, int $sessionId, int $carteId): RedirectResponse
    {
        $session = $this->sessionAutorisee($sessionId);
        $this->carteAutorisee($session->id, $carteId);

        $donnees = $request->validate(['direction' => ['required', 'in:up,down']]);
        $this->depot->deplacerCarte($carteId, (string) $donnees['direction']);

        return back();
    }

    private function stocker(Request $request, int $sessionId): string
    {
        $file = $request->file('image');
        $extension = strtolower((string) $file->getClientOriginalExtension());

        return $file->storeAs(
            'outils/tri-cartes/session_'.$sessionId,
            now()->format('Ymd_His').'_'.Str::random(8).'.'.$extension,
            'public'
        );
    }

    private function sessionAutorisee(int $sessionId): object
    {
        $session = $this->depot->trouver($sessionId);
        abort_unless($session, 404);
        $this->acces->verifierFormateur($session->group_id, (int) auth()->id());

        return $session;
    }

    private function carteAutorisee(int $sessionId, int $carteId): object
    {
        $carte = $this->depot->trouverCarte($carteId);
        abort_unless($carte && (int) $carte->card_sort_session_id === $sessionId, 404);

        return $carte;
    }
}
