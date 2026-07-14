<?php

namespace App\Domains\Outils\Carrousel\Http\Controllers\Formateur;

use App\Domains\Outils\Carrousel\Support\AccesCarrousel;
use App\Domains\Outils\Carrousel\Support\DepotCarrousel;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CarrouselSlideController extends Controller
{
    public function __construct(
        private readonly DepotCarrousel $depot,
        private readonly AccesCarrousel $acces,
    ) {}

    public function store(Request $request, int $sessionId): RedirectResponse
    {
        $session = $this->sessionAutorisee($sessionId);

        $donnees = $request->validate([
            'text' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,avif', 'max:4096'],
        ]);

        $texte = trim((string) ($donnees['text'] ?? ''));
        if ($texte === '' && ! $request->hasFile('image')) {
            return back()->withErrors(['text' => 'Ajoutez au moins un texte ou une image.'])->withInput();
        }

        $this->depot->ajouterSlide($session->id, [
            'text' => $texte ?: null,
            'image_path' => $request->hasFile('image')
                ? $this->stocker($request, $session->id)
                : null,
        ]);

        return back()->with('success', 'Slide ajoutée.');
    }

    public function update(Request $request, int $sessionId, int $slideId): RedirectResponse
    {
        $session = $this->sessionAutorisee($sessionId);
        $slide = $this->slideAutorisee($session->id, $slideId);

        $donnees = $request->validate([
            'text' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,avif', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $update = ['text' => trim((string) ($donnees['text'] ?? '')) ?: null];

        if ($request->hasFile('image')) {
            if (! empty($slide->image_path)) {
                Storage::disk('public')->delete($slide->image_path);
            }
            $update['image_path'] = $this->stocker($request, $session->id);
        } elseif ($request->boolean('remove_image') && ! empty($slide->image_path)) {
            Storage::disk('public')->delete($slide->image_path);
            $update['image_path'] = null;
        }

        $this->depot->modifierSlide($slideId, $update);

        return back()->with('success', 'Slide mise à jour.');
    }

    public function destroy(int $sessionId, int $slideId): RedirectResponse
    {
        $session = $this->sessionAutorisee($sessionId);
        $slide = $this->slideAutorisee($session->id, $slideId);

        if (! empty($slide->image_path)) {
            Storage::disk('public')->delete($slide->image_path);
        }

        $this->depot->supprimerSlide($slideId);

        return back()->with('success', 'Slide supprimée.');
    }

    public function move(Request $request, int $sessionId, int $slideId): RedirectResponse
    {
        $session = $this->sessionAutorisee($sessionId);
        $this->slideAutorisee($session->id, $slideId);

        $donnees = $request->validate(['direction' => ['required', 'in:up,down']]);
        $this->depot->deplacerSlide($slideId, (string) $donnees['direction']);

        return back();
    }

    private function stocker(Request $request, int $sessionId): string
    {
        $file = $request->file('image');
        $extension = strtolower((string) $file->getClientOriginalExtension());

        return $file->storeAs(
            'outils/carrousel/session_'.$sessionId,
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

    private function slideAutorisee(int $sessionId, int $slideId): object
    {
        $slide = $this->depot->trouverSlide($slideId);
        abort_unless($slide && (int) $slide->carousel_session_id === $sessionId, 404);

        return $slide;
    }
}
