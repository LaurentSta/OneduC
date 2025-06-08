<?php

// app/Http/Controllers/Backend/EvaluationController.php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Evaluation;

class EvaluationController extends Controller
{
    public function index()
    {
        $evaluations = Evaluation::withCount('modules')->latest()->get();
        return view('admin.backend.evaluations.index', compact('evaluations'));
    }


    public function create()
    {
        return view('admin.backend.evaluations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'scorm_path' => 'required|string|max:255',
        ]);

        Evaluation::create([
            'titre' => $request->titre,
            'scorm_path' => $request->scorm_path,
        ]);


        return redirect()->route('admin.evaluations.index')->with('success', 'Évaluation ajoutée avec succès.');
    }


    public function destroy($id)
    {
        Evaluation::findOrFail($id)->delete();
        return back()->with('success', 'Évaluation supprimée.');
    }

    public function edit($id)
    {
        $evaluation = Evaluation::findOrFail($id);
        return view('admin.backend.evaluations.edit', compact('evaluation'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'scorm_path' => 'required|string|max:255',
        ]);

        $evaluation = Evaluation::findOrFail($id);
        $evaluation->update([
            'titre' => $request->titre,
            'scorm_path' => $request->scorm_path,
        ]);

        return redirect()->route('admin.evaluations.index')->with('success', 'Évaluation mise à jour avec succès.');
    }

    public function show($id)
    {
        $evaluation = Evaluation::findOrFail($id);
        return view('frontend.modules.evaluations.show', compact('evaluation'));
    }



}
