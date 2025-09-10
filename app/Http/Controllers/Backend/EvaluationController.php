<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Evaluation;

class EvaluationController extends Controller
{
    /** Base de vues selon rôle connecté. */
    private function viewBase(): string
    {
        $role = optional(auth()->user())->role; // 'admin' | 'formateur' | 'stagiaire'
        return $role === 'formateur' ? 'formateur.formations' : 'stagiaire.formations';
    }

    /** Liste paginée. */
    public function index()
    {
        $evaluations = Evaluation::withCount('modules')
            ->orderBy('titre')
            ->paginate(50);

        return view('admin.backend.evaluations.index', compact('evaluations'));
    }

    /** Formulaire de création. */
    public function create()
    {
        return view('admin.backend.evaluations.create');
    }

    /** Enregistrement. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'      => ['required','string','max:255'],
            // dossier uniquement: lettres, chiffres, ., _, -
            'scorm_path' => ['required','string','max:255','regex:/^[A-Za-z0-9._-]+$/','unique:evaluations,scorm_path'],
        ]);

        Evaluation::create($data);

        return redirect()->route('admin.evaluations.index')
            ->with('success', 'Évaluation ajoutée avec succès.');
    }

    /** Formulaire d’édition. */
    public function edit(Evaluation $evaluation)
    {
        return view('admin.backend.evaluations.edit', compact('evaluation'));
    }

    /** Mise à jour. */
    public function update(Request $request, Evaluation $evaluation)
    {
        $data = $request->validate([
            'titre'      => ['required','string','max:255'],
            'scorm_path' => [
                'required','string','max:255','regex:/^[A-Za-z0-9._-]+$/',
                'unique:evaluations,scorm_path,'.$evaluation->id,
            ],
        ]);

        $evaluation->update($data);

        return redirect()->route('admin.evaluations.index')
            ->with('success', 'Évaluation mise à jour avec succès.');
    }

    /** Suppression. */
    public function destroy(Evaluation $evaluation)
    {
        $evaluation->delete();
        return back()->with('success', 'Évaluation supprimée.');
    }

    /** Affichage élève/formateur. */
    public function show($id)
    {
        $evaluation = \App\Models\Evaluation::findOrFail($id); // ← récupère par l’ID de l’URL

        $base = $this->viewBase(); // 'formateur.formations' | 'stagiaire.formations'
        return view("$base.evaluations.show", compact('evaluation'));
    }
}
