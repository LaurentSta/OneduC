<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Groupe;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class InstructorGroupeController extends Controller
{
    // 🟠 Liste des groupes de l’instructeur
    public function index()
    {
        $groupes = Groupe::whereHas('stagiaires', function ($query) {
            $query->where('users.id', Auth::id()); // Afficher uniquement les groupes de l'instructeur
        })->with('stagiaires')->get();

        return view('frontend.instructor.all_groupes', compact('groupes'));
    }

    // 🟠 Formulaire de création d’un groupe
    public function create()
    {
        $stagiaires = User::where('role', 'stagiaire')->get(); // Lister les stagiaires disponibles
        return view('frontend.instructor.add_groupe', compact('stagiaires'));
    }

    // 🟠 Enregistrement d’un groupe avec des stagiaires
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255|unique:groupes',
            'stagiaires' => 'array',
        ]);

        $groupe = Groupe::create(['nom' => $request->nom]);

        if ($request->has('stagiaires')) {
            $groupe->stagiaires()->sync($request->stagiaires);
        }

        return redirect()->route('instructor.groupes.index')->with('success', 'Groupe créé avec succès');
    }

    // 🟠 Formulaire de modification d’un groupe (restriction)
    public function edit(Groupe $groupe)
    {
        if (!$groupe->stagiaires->contains(Auth::id())) {
            return redirect()->route('instructor.groupes.index')->with('error', 'Accès refusé.');
        }

        $stagiaires = User::where('role', 'stagiaire')->get(); // Lister les stagiaires disponibles
        return view('frontend.instructor.edit_groupe', compact('groupe', 'stagiaires'));
    }

    // 🟠 Mise à jour d’un groupe (restriction)
    public function update(Request $request, Groupe $groupe)
    {
        if (!$groupe->stagiaires->contains(Auth::id())) {
            return redirect()->route('instructor.groupes.index')->with('error', 'Accès refusé.');
        }

        $request->validate([
            'nom' => 'required|string|max:255|unique:groupes,nom,' . $groupe->id,
            'stagiaires' => 'array',
        ]);

        $groupe->update(['nom' => $request->nom]);
        $groupe->stagiaires()->sync($request->stagiaires);

        return redirect()->route('instructor.groupes.index')->with('success', 'Groupe mis à jour');
    }
}
