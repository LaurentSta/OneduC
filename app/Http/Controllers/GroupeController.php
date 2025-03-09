<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Groupe;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GroupeController extends Controller
{
    // 🟢 Liste des groupes (Admin voit tout)
    public function index()
    {
        $groupes = Groupe::all(); // ✅ Récupère tous les groupes
        return view('admin.backend.groupes.all_groupes', compact('groupes'));
    }

    // 🟢 Formulaire de création d’un groupe (sans stagiaires)
    public function create()
    {
        return view('admin.backend.groupes.add_groupe');
    }

    // 🟢 Enregistrement d’un groupe
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255|unique:groupes',
        ]);

        Groupe::create(['nom' => $request->nom]);

        return redirect()->route('admin.groupes.index')->with('success', 'Groupe créé avec succès');
    }

    // 🟢 Formulaire de modification d’un groupe
    public function edit(Groupe $groupe)
    {
        return view('admin.backend.groupes.edit_groupe', compact('groupe'));
    }

    // 🟢 Mise à jour d’un groupe
    public function update(Request $request, Groupe $groupe)
    {
        $request->validate([
            'nom' => 'required|string|max:255|unique:groupes,nom,' . $groupe->id,
        ]);

        $groupe->update(['nom' => $request->nom]);

        return redirect()->route('admin.groupes.index')->with('success', 'Groupe mis à jour');
    }

    // 🟢 Suppression d’un groupe
    public function destroy(Groupe $groupe)
    {
        $groupe->delete();
        return redirect()->route('admin.groupes.index')->with('success', 'Groupe supprimé');
    }

    // 🟢 Formulaire pour ajouter des stagiaires à un groupe existant
    public function assignStagiaires($id)
    {
        $groupe = Groupe::findOrFail($id);
        $stagiaires = User::where('role', 'stagiaire')->get();

        return view('admin.backend.groupes.assign_stagiaires', compact('groupe', 'stagiaires'));
    }

    // 🟢 Enregistrement des stagiaires dans un groupe existant
    public function storeStagiaires(Request $request, $id)
    {
        $groupe = Groupe::findOrFail($id);

        $request->validate([
            'stagiaires' => 'array',
        ]);

        $groupe->stagiaires()->sync($request->stagiaires);

        return redirect()->route('admin.groupes.index')->with('success', 'Stagiaires assignés au groupe.');
    }
}
