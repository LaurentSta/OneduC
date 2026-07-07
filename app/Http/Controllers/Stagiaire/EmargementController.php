<?php

namespace App\Http\Controllers\Stagiaire;

use App\Domains\Outils\Emargement\Actions\SignerPresence;
use App\Domains\Outils\Emargement\Support\AccesEmargement;
use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmargementController extends Controller
{
    public function __construct(private readonly AccesEmargement $acces) {}

    public function show(Group $group): View
    {
        $this->acces->assertStagiaireAccess($group);

        $seance = $group->seances()->where('statut', 'ouverte')->latest('date')->first();

        $presence = $seance?->presences()->where('user_id', auth()->id())->first();

        return view('stagiaire.emargement.show', [
            'group' => $group,
            'seance' => $seance,
            'presence' => $presence,
        ]);
    }

    public function signer(Request $request, Group $group, SignerPresence $signerPresence): RedirectResponse
    {
        $this->acces->assertStagiaireAccess($group);

        $data = $request->validate([
            'signature' => ['required', 'string'],
        ]);

        $seance = $group->seances()->where('statut', 'ouverte')->latest('date')->firstOrFail();
        $presence = $seance->presences()->where('user_id', auth()->id())->firstOrFail();

        $signerPresence->execute($presence, $request->user(), $data['signature']);

        return redirect()
            ->route('stagiaire.emargement.show', $group->id)
            ->with('success', 'Votre présence a bien été signée.');
    }
}
