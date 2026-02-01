<?php
// /home/laurents/Oneduc_Dev/app/Http/Controllers/Backend/GroupeController.php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User; // Ajout pour récupérer les formateurs et stagiaires
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class GroupeController extends Controller
{
    /**
     * Afficher la liste des groupes (si besoin)
     */
    public function AllGroupe()
    {
        $groupes = Group::all();
        return view('admin.backend.groupes.groupes', compact('groupes'));
    }

    /**
     * Afficher le formulaire de création d'un groupe
     */
    public function AddGroupe()
    {
        // Récupération des formateurs (avec un rôle 'formateur' par exemple)
        $formateurs = User::where('role', 'formateur')->get();

        // Récupération des stagiaires (avec un rôle 'stagiaire' par exemple)
        $stagiaires = User::where('role', 'stagiaire')->get();

        return view('admin.backend.groupes.add_groupe', compact('formateurs', 'stagiaires'));
    }

    /**
     * Enregistrer un nouveau groupe
     */
    public function StoreGroupe(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'formateur_id' => 'required|exists:users,id',
            'stagiaires' => 'nullable|array',
            'stagiaires.*' => 'exists:users,id',
            'groupe_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $imagePath = $request->hasFile('groupe_image')
            ? $request->file('groupe_image')->store('groupe_images', 'public')
            : null;

        $group = Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'instructor_id' => $request->formateur_id,
            'groupe_image' => $imagePath,
        ]);

        // Attacher les stagiaires si besoin
        if ($request->has('stagiaires')) {
            $group->students()->sync($request->stagiaires);
        }

        return redirect()->route('groupes')->with('success', 'Groupe ajouté avec succès 🎉');
    }

    /**
     * Afficher le formulaire d'édition d'un groupe
     */
    public function EditGroupe($id)
    {
        $groupe = Group::findOrFail($id);

        // Récupération des formateurs
        $formateurs = User::where('role', 'formateur')->get();

        // Récupération des stagiaires
        $stagiaires = User::where('role', 'stagiaire')->get();

        return view('admin.backend.groupes.edit_groupe', compact('groupe', 'formateurs', 'stagiaires'));
    }

    /**
     * Mettre à jour un groupe existant
     */
    public function UpdateGroupe(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:groups,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'formateur_id' => 'required|exists:users,id',
            'stagiaires' => 'nullable|array',
            'stagiaires.*' => 'exists:users,id',
            'groupe_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $groupe = Group::findOrFail($request->id);

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'instructor_id' => $request->formateur_id,
        ];

        if ($request->hasFile('groupe_image')) {
            if ($groupe->groupe_image && Storage::disk('public')->exists($groupe->groupe_image)) {
                Storage::disk('public')->delete($groupe->groupe_image);
            }

            $data['groupe_image'] = $request->file('groupe_image')->store('groupe_images', 'public');
        }

        $groupe->update($data);

        // Synchroniser les stagiaires
        if ($request->has('stagiaires')) {
            $groupe->students()->sync($request->stagiaires);
        }

        return redirect()->route('groupes')->with('success', 'Groupe mis à jour avec succès 🎉');
    }

    /**
     * Supprimer un groupe
     */
    public function destroy($id)
    {
        $groupe = Group::findOrFail($id);

        // Supprimer l'image
        if ($groupe->groupe_image && Storage::disk('public')->exists($groupe->groupe_image)) {
            Storage::disk('public')->delete($groupe->groupe_image);
        }

        // Détacher les relations (facultatif selon ta logique)
        $groupe->students()->detach();

        $groupe->delete();

        return redirect()->route('formateur.groupes.index')->with('success', 'Groupe supprimé avec succès.');

    }




}
