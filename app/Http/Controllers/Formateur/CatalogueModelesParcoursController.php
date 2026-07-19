<?php

namespace App\Http\Controllers\Formateur;

use App\Domains\Parcours\Actions\DupliquerModeleParcours;
use App\Http\Controllers\Controller;
use App\Models\ModeleParcours;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CatalogueModelesParcoursController extends Controller
{
    public function index(): View
    {
        $modeles = ModeleParcours::query()
            ->publies()
            ->with('auteur:id,prenom,name,username')
            ->withCount('items')
            ->orderByDesc('publie_le')
            ->paginate(12);

        return view('formateur.mes-parcours.catalogue-modeles', compact('modeles'));
    }

    public function dupliquer(
        ModeleParcours $modele,
        DupliquerModeleParcours $dupliquerModeleParcours
    ): RedirectResponse {
        abort_unless($modele->estPublie(), 404);

        $parcours = $dupliquerModeleParcours->executer($modele, auth()->user());

        return redirect()
            ->route('formateur.mes-parcours.index')
            ->with('success', "Le modèle « {$modele->titre} » a été copié dans vos parcours.");
    }
}
