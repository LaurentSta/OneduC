<?php

namespace App\Domains\Outils\CartesRetourner\Http\Controllers\Formateur;

use App\Domains\Outils\CartesRetourner\Support\AccesCartesRetourner;
use App\Domains\Outils\CartesRetourner\Support\DepotCartesRetourner;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CartesRetournerCarteController extends Controller
{
    public function __construct(
        private readonly DepotCartesRetourner $depot,
        private readonly AccesCartesRetourner $acces,
    ) {}

    public function store(Request $request, int $sessionId): RedirectResponse
    {
        $session = $this->sessionAutorisee($sessionId);

        $donnees = $request->validate([
            'recto_text' => ['nullable', 'string', 'max:1000'],
            'recto_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,avif', 'max:4096'],
            'verso_text' => ['nullable', 'string', 'max:1000'],
            'verso_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,avif', 'max:4096'],
        ]);

        $rectoTexte = trim((string) ($donnees['recto_text'] ?? ''));
        $versoTexte = trim((string) ($donnees['verso_text'] ?? ''));

        if ($rectoTexte === '' && ! $request->hasFile('recto_image')) {
            return back()->withErrors(['recto_text' => 'Le recto doit contenir un texte ou une image.'])->withInput();
        }

        if ($versoTexte === '' && ! $request->hasFile('verso_image')) {
            return back()->withErrors(['verso_text' => 'Le verso doit contenir un texte ou une image.'])->withInput();
        }

        $this->depot->ajouterCarte($session->id, [
            'recto_text' => $rectoTexte ?: null,
            'recto_image_path' => $request->hasFile('recto_image') ? $this->stocker($request, 'recto_image', $session->id) : null,
            'verso_text' => $versoTexte ?: null,
            'verso_image_path' => $request->hasFile('verso_image') ? $this->stocker($request, 'verso_image', $session->id) : null,
        ]);

        return back()->with('success', 'Carte ajoutée.');
    }

    public function destroy(int $sessionId, int $carteId): RedirectResponse
    {
        $session = $this->sessionAutorisee($sessionId);
        $carte = $this->carteAutorisee($session->id, $carteId);

        foreach ([$carte->recto_image_path, $carte->verso_image_path] as $chemin) {
            if (! empty($chemin)) {
                Storage::disk('public')->delete($chemin);
            }
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

    private function stocker(Request $request, string $champ, int $sessionId): string
    {
        $file = $request->file($champ);
        $extension = strtolower((string) $file->getClientOriginalExtension());

        return $file->storeAs(
            'outils/cartes-retourner/session_'.$sessionId,
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
        abort_unless($carte && (int) $carte->flashcard_session_id === $sessionId, 404);

        return $carte;
    }
}
