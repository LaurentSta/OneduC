<?php

namespace App\Http\Controllers\Stagiaire;

use App\Domains\Outils\Emargement\Actions\SignerPresence;
use App\Domains\Outils\Emargement\Support\AccesEmargement;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Seance;
use Illuminate\Http\JsonResponse;
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
        $presence = $seance->presences()->where('user_id', auth()->id())->first();

        if (! $presence) {
            return redirect()
                ->route('stagiaire.emargement.show', $group->id)
                ->with('error', "Vous n'êtes pas inscrit·e à cette séance. Contactez votre formateur.");
        }

        $signerPresence->execute($presence, $request->user(), $data['signature']);

        return redirect()
            ->route('stagiaire.emargement.show', $group->id)
            ->with('success', 'Votre présence a bien été signée.');
    }

    public function notificationStatus(): JsonResponse
    {
        $userId = auth()->id();
        $activeGroupIds = auth()->user()->groupesStagiaire()->active()->pluck('groups.id');

        $seance = Seance::query()
            ->where('statut', 'ouverte')
            ->whereIn('group_id', $activeGroupIds)
            ->whereHas('presences', fn ($q) => $q->where('user_id', $userId)->where('statut', 'en_attente'))
            ->with('group')
            ->latest('opened_at')
            ->first();

        return response()->json([
            'has_open_seance' => ! is_null($seance),
            'group_name' => $seance?->group?->name,
            'opened_at_human' => $seance?->opened_at?->diffForHumans(),
            'join_url' => $seance ? route('stagiaire.emargement.show', ['group' => $seance->group_id]) : null,
        ]);
    }
}
