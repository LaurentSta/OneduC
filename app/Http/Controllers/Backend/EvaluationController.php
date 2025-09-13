<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Evaluation;
use App\Models\ScormEvaluationScore;
use App\Models\ScormEvaluationInteraction;

class EvaluationController extends Controller
{
    /**
     * Base de vues selon rôle connecté.
     * Retourne 'formateur.formations' ou 'stagiaire.formations'.
     */
    private function viewBase(): string
    {
        $role = optional(auth()->user())->role; // 'admin' | 'formateur' | 'stagiaire'
        return $role === 'formateur' ? 'formateur.formations' : 'stagiaire.formations';
    }

    /** Liste paginée des évaluations (côté admin). */
    public function index()
    {
        $evaluations = Evaluation::withCount('modules')
            ->orderBy('titre')
            ->paginate(50);

        return view('admin.backend.evaluations.index', compact('evaluations'));
    }

    /** Formulaire de création (admin). */
    public function create()
    {
        return view('admin.backend.evaluations.create');
    }

    /** Enregistrement (admin). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'      => ['required', 'string', 'max:255'],
            // dossier uniquement: lettres, chiffres, ., _, -
            'scorm_path' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:evaluations,scorm_path'],
        ]);

        Evaluation::create($data);

        return redirect()->route('admin.evaluations.index')
            ->with('success', 'Évaluation ajoutée avec succès.');
    }

    /** Formulaire d’édition (admin). */
    public function edit(Evaluation $evaluation)
    {
        return view('admin.backend.evaluations.edit', compact('evaluation'));
    }

    /** Mise à jour (admin). */
    public function update(Request $request, Evaluation $evaluation)
    {
        $data = $request->validate([
            'titre'      => ['required', 'string', 'max:255'],
            'scorm_path' => [
                'required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/',
                'unique:evaluations,scorm_path,' . $evaluation->id,
            ],
        ]);

        $evaluation->update($data);

        return redirect()->route('admin.evaluations.index')
            ->with('success', 'Évaluation mise à jour avec succès.');
    }

    /** Suppression (admin). */
    public function destroy(Evaluation $evaluation)
    {
        $evaluation->delete();
        return back()->with('success', 'Évaluation supprimée.');
    }

    /**
     * Affichage élève/formateur d’une évaluation SCORM.
     * Route actuelle: /evaluations/{id}
     */
    public function show($id)
    {
        $evaluation = Evaluation::findOrFail($id);

        $base = $this->viewBase(); // 'formateur.formations' | 'stagiaire.formations'
        return view("$base.evaluations.show", compact('evaluation'));
    }

    /**
     * Page de fin d’évaluation (félicitations + récap).
     * Route prévue: route('stagiaire.evaluations.fin', $evaluation)
     */
    public function fin(Evaluation $evaluation)
    {
        $userId = auth()->id();

        $score = ScormEvaluationScore::where('user_id', $userId)
            ->where('evaluation_id', $evaluation->id)
            ->first();

        $answered = ScormEvaluationInteraction::where('user_id', $userId)
            ->where('evaluation_id', $evaluation->id)
            ->count();

        $base = $this->viewBase(); // 'formateur.formations' | 'stagiaire.formations'

        return view("$base.evaluations.fin_evaluation", [
            'evaluation'         => $evaluation,
            'lastScore'          => $score?->last_score,
            'bestScore'          => $score?->best_score,
            'attempts'           => $score?->attempts_count ?? 1,
            'sessionTimeSeconds' => $score?->session_time ?? 0,
            'questionsAnswered'  => $answered,
        ]);
    }
}
