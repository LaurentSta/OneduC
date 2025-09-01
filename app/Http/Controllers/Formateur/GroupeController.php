<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\CodeGeneratorService;
use Illuminate\Validation\Rule;


class GroupeController extends Controller
{
    /**
     * Affiche le formulaire du wizard
     */
   
    public function create()
    {
        $modules = Module::active()->orderBy('module_title')->get();
        return view('formateur.groupes.create', compact('modules'));
    }

    /**
     * Enregistre un groupe + stagiaires + modules
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|unique:groups,name',
            'description' => 'nullable|string',
            'password' => 'required|string|min:8',
            'modules' => 'required|array|min:1',
            'modules.*' => Rule::exists('modules', 'id')->where('status', 1),
            'stagiaires' => 'required|array|min:1',
            'stagiaires.*.email' => 'required|email|distinct',
            'stagiaires.*.prenom' => 'required|string|max:255',
            'stagiaires.*.nom' => 'required|string|max:255',
        ]);
        $group = Group::create([
            'name' => $request->nom,
            'description' => $request->description,
            'instructor_id' => auth()->id(),
        ]);

        foreach ($request->stagiaires as $stagiaireData) {
            $user = User::where('email', $stagiaireData['email'])->first();

            if (!$user) {
                            $user = new User();
                            $user->prenom = $stagiaireData['prenom'];
                            $user->name = $stagiaireData['nom'];
                            $user->email = $stagiaireData['email'];
                            $user->password = Hash::make($request->password);
                            $user->role = 'stagiaire';
                            $user->formateur_id = auth()->id();
                            $user->code_acces = CodeGeneratorService::generateUniqueAccessCode();
                            $user->save();
                        }


            $group->students()->syncWithoutDetaching([
                $user->id => ['role_in_group' => 'stagiaire']
            ]);

        }

        $group->modules()->sync($request->modules);

        return redirect()->route('formateur.groupes.index')->with('success', 'Groupe et stagiaires enregistrés avec succès 🎉');
    }


    /**
     * Affiche la liste des groupes du formateur
     */
    public function index()
        {
            $groupes = Group::where('instructor_id', auth()->id())->with('modules', 'students')->get();

            return view('formateur.groupes.index', compact('groupes'));
        }
    public function edit($id)
        {
            $group = Group::where('id', $id)
                ->where('instructor_id', auth()->id())
                ->with('modules', 'students')
                ->firstOrFail();

            $modules = Module::active()->orderBy('module_title')->get();


            return view('formateur.groupes.edit', compact('group', 'modules'));
        }

        public function update(Request $request, $id)
{
    $request->validate([
        'nom' => 'required|string|unique:groups,name,' . $id,
        'description' => 'nullable|string',
        'modules' => 'required|array|min:1',
        'modules.*' => Rule::exists('modules', 'id')->where('status', 1),
        'stagiaires.*.email' => 'nullable|email|distinct',
        'stagiaires.*.prenom' => 'nullable|string|max:255',
        'stagiaires.*.nom' => 'nullable|string|max:255',
    ]);

    // ⚠️ Vérification d’appartenance AVANT toute mise à jour
    $group = Group::where('id', $id)
        ->where('instructor_id', auth()->id())
        ->firstOrFail();

    $group->update([
        'name' => $request->nom,
        'description' => $request->description,
    ]);

    $group->modules()->sync($request->modules);

    if ($request->has('stagiaires')) {
        foreach ($request->stagiaires as $stagiaireData) {
            // 🔒 On ignore les blocs vides
            if (
                empty($stagiaireData['prenom']) &&
                empty($stagiaireData['nom']) &&
                empty($stagiaireData['email'])
            ) {
                continue;
            }

            $user = User::where('email', $stagiaireData['email'])->first();

            if (!$user) {
                $user = new User();
                $user->prenom = $stagiaireData['prenom'];
                $user->name = $stagiaireData['nom'];
                $user->email = $stagiaireData['email'];
                $user->password = Hash::make(Str::random(10)); // mot de passe temporaire
                $user->role = 'stagiaire';
                $user->formateur_id = auth()->id();
                $user->code_acces = CodeGeneratorService::generateUniqueAccessCode();
                $user->save();
            }

            $group->students()->syncWithoutDetaching([
                $user->id => ['role_in_group' => 'stagiaire']
            ]);
        }
    }

    return redirect()->route('formateur.groupes.index')->with('success', 'Groupe modifié avec succès ✅');
}


        public function destroy($id)
        {
            $groupe = Group::findOrFail($id);

            if ($groupe->groupe_image && Storage::disk('public')->exists($groupe->groupe_image)) {
                Storage::disk('public')->delete($groupe->groupe_image);
            }

            $groupe->students()->detach();
            $groupe->delete();

            return redirect()->route('formateur.groupes.index')->with('success', 'Groupe supprimé avec succès.');
        }





}
