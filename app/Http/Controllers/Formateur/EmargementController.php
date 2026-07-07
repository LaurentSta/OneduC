<?php

namespace App\Http\Controllers\Formateur;

use App\Domains\Outils\Emargement\Actions\ClorerSeance;
use App\Domains\Outils\Emargement\Actions\CorrigerPresence;
use App\Domains\Outils\Emargement\Actions\CreerSeance;
use App\Domains\Outils\Emargement\Actions\OuvrirSeance;
use App\Domains\Outils\Emargement\Support\AccesEmargement;
use App\Domains\Outils\Emargement\Support\GenererPdfEmargement;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Seance;
use App\Models\SeancePresence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class EmargementController extends Controller
{
    public function __construct(private readonly AccesEmargement $acces) {}

    public function index(Request $request): View
    {
        $formateurId = (int) auth()->id();

        $groupes = Group::query()
            ->accessibleByTrainer($formateurId)
            ->withCount('students')
            ->orderBy('name')
            ->get();

        $selectedGroup = $groupes->firstWhere('id', (int) $request->query('group_id'));

        $seances = ($selectedGroup && $selectedGroup->emargement_enabled)
            ? Seance::where('group_id', $selectedGroup->id)
                ->withCount(['presences', 'presences as presences_signees_count' => fn ($q) => $q->where('statut', 'present')])
                ->orderByDesc('date')
                ->get()
            : collect();

        return view('formateur.emargement.index', [
            'groupes' => $groupes,
            'selectedGroup' => $selectedGroup,
            'seances' => $seances,
        ]);
    }

    public function activerGroupe(Group $group): RedirectResponse
    {
        $group = $this->acces->assertFormateurAccess($group);
        $group->update(['emargement_enabled' => true]);

        return redirect()->route('formateur.emargement.index', ['group_id' => $group->id]);
    }

    public function desactiverGroupe(Group $group): RedirectResponse
    {
        $group = $this->acces->assertFormateurAccess($group);
        $group->update(['emargement_enabled' => false]);

        return redirect()->route('formateur.emargement.index')->with('success', 'Émargement désactivé pour ce groupe.');
    }

    public function store(Request $request, Group $group, CreerSeance $creerSeance): RedirectResponse
    {
        $group = $this->acces->assertFormateurAccess($group);

        $data = $request->validate([
            'date' => ['required', 'date'],
            'creneau' => [
                'required',
                'in:matin,apres_midi,journee,soiree',
                Rule::unique('seances')->where(
                    fn ($query) => $query->where('group_id', $group->id)->where('date', $request->input('date'))
                ),
            ],
            'heure_debut' => ['nullable', 'date_format:H:i'],
            'heure_fin' => ['nullable', 'date_format:H:i'],
            'titre' => ['nullable', 'string', 'max:255'],
        ], [
            'creneau.unique' => 'Une séance existe déjà pour cette date et ce créneau.',
        ]);

        $creerSeance->execute($group, $data, $request->user());

        return redirect()
            ->route('formateur.emargement.index', ['group_id' => $group->id])
            ->with('success', 'Séance créée.');
    }

    public function show(Group $group, Seance $seance): View
    {
        $group = $this->acces->assertFormateurAccess($group);
        abort_unless($seance->group_id === $group->id, 404);

        $seance->load(['presences' => fn ($q) => $q->orderBy('stagiaire_nom_snapshot')]);

        return view('formateur.emargement.show', [
            'group' => $group,
            'seance' => $seance,
            'stateUrl' => route('formateur.groupes.emargement.state', [$group->id, $seance->id]),
            'corrigerUrlTemplate' => route('formateur.groupes.emargement.presences.corriger', [$group->id, $seance->id, 'PRESENCE_ID']),
        ]);
    }

    public function state(Group $group, Seance $seance): JsonResponse
    {
        $group = $this->acces->assertFormateurAccess($group);
        abort_unless($seance->group_id === $group->id, 404);

        $seance->load(['presences' => fn ($q) => $q->orderBy('stagiaire_nom_snapshot')]);

        return response()->json([
            'statut' => $seance->statut,
            'presences' => $seance->presences->map(fn ($presence) => [
                'id' => $presence->id,
                'nom' => $presence->stagiaire_nom_snapshot,
                'statut' => $presence->statut,
                'signature_type' => $presence->signature_type,
                'signature' => $presence->signatureBase64,
                'motif_absence' => $presence->motif_absence,
            ]),
        ]);
    }

    public function ouvrir(Group $group, Seance $seance, OuvrirSeance $ouvrirSeance): RedirectResponse
    {
        $group = $this->acces->assertFormateurAccess($group);
        abort_unless($seance->group_id === $group->id, 404);

        $ouvrirSeance->execute($seance);

        return redirect()
            ->route('formateur.groupes.emargement.show', [$group->id, $seance->id])
            ->with('success', 'Séance ouverte : les stagiaires peuvent signer.');
    }

    public function fermer(Group $group, Seance $seance, ClorerSeance $clorerSeance): RedirectResponse
    {
        $group = $this->acces->assertFormateurAccess($group);
        abort_unless($seance->group_id === $group->id, 404);

        $clorerSeance->execute($seance);

        return redirect()
            ->route('formateur.groupes.emargement.show', [$group->id, $seance->id])
            ->with('success', 'Séance clôturée.');
    }

    public function corrigerPresence(
        Request $request,
        Group $group,
        Seance $seance,
        SeancePresence $presence,
        CorrigerPresence $corrigerPresence,
    ): RedirectResponse {
        $group = $this->acces->assertFormateurAccess($group);
        abort_unless($seance->group_id === $group->id, 404);
        abort_unless($presence->seance_id === $seance->id, 404);

        $data = $request->validate([
            'statut' => ['required', 'in:present,absent'],
            'signature' => ['nullable', 'string', 'required_if:statut,present'],
            'motif_absence' => ['nullable', 'string', 'max:255'],
        ]);

        $corrigerPresence->execute(
            $presence,
            $request->user(),
            $data['statut'],
            $data['signature'] ?? null,
            $data['motif_absence'] ?? null,
        );

        return redirect()
            ->route('formateur.groupes.emargement.show', [$group->id, $seance->id])
            ->with('success', 'Présence mise à jour.');
    }

    public function exportPdf(Group $group, Seance $seance, GenererPdfEmargement $genererPdf): Response
    {
        $group = $this->acces->assertFormateurAccess($group);
        abort_unless($seance->group_id === $group->id, 404);

        return $genererPdf->execute($seance)->download("emargement-{$seance->reference}.pdf");
    }
}
