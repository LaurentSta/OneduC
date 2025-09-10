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
     * Liste des groupes du formateur.
     */
    public function index()
    {
        $groupes = Group::where('instructor_id', auth()->id())
            ->with(['modules', 'students'])
            ->get();

        return view('formateur.groupes.index', compact('groupes'));
    }

    /**
     * Formulaire de création (wizard).
     */
    public function create()
    {
        $modules = Module::active()->orderBy('module_title')->get();
        return view('formateur.groupes.create', compact('modules'));
    }

    /**
     * Enregistrer un groupe + stagiaires + modules.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom'                   => ['required','string','max:150','unique:groups,name'],
            'description'           => ['nullable','string','max:2000'],
            'password'              => ['required','string','min:8'],
            'modules'               => ['required','array','min:1'],
            'modules.*'             => [Rule::exists('modules','id')->where('status', 1)],
            'stagiaires'            => ['required','array','min:1'],
            'stagiaires.*.email'    => ['required','email','distinct'],
            'stagiaires.*.prenom'   => ['required','string','max:255'],
            'stagiaires.*.nom'      => ['required','string','max:255'],
        ]);

        // Création du groupe
        $group = Group::create([
            'name'          => $request->nom,
            'description'   => $request->description,
            'instructor_id' => auth()->id(),
        ]);

        // Ajout / rattachement des stagiaires
        foreach ($request->stagiaires as $idx => $s) {
            // ignore les blocs vides par prudence
            if (empty($s['email']) && empty($s['prenom']) && empty($s['nom'])) {
                continue;
            }

            $user = User::where('email', $s['email'])->first();

            if (!$user) {
                $user = new User();
                $user->prenom       = $s['prenom'];
                $user->name         = $s['nom'];
                $user->email        = $s['email'];
                $user->password     = Hash::make($request->password); // mot de passe commun (première connexion)
                $user->role         = 'stagiaire';
                $user->formateur_id = auth()->id();
                $user->code_acces   = CodeGeneratorService::generateUniqueAccessCode();
                $user->save();
            }

            // Attache sans dupliquer
            $group->students()->syncWithoutDetaching([
                $user->id => ['role_in_group' => 'stagiaire']
            ]);
        }

        // Association des modules
        $group->modules()->sync($request->modules);

        return redirect()
            ->route('formateur.groupes.index')
            ->with('success', 'Groupe et stagiaires enregistrés avec succès.');
    }

    /**
     * Formulaire d’édition.
     */
    public function edit($id)
    {
        $group = Group::where('id', $id)
            ->where('instructor_id', auth()->id())
            ->with(['modules', 'students'])
            ->firstOrFail();

        $modules = Module::active()->orderBy('module_title')->get();

        return view('formateur.groupes.edit', compact('group', 'modules'));
    }

    /**
     * Mettre à jour un groupe + rattachements.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nom'                   => ['required','string','max:150',"unique:groups,name,{$id}"],
            'description'           => ['nullable','string','max:2000'],
            'modules'               => ['required','array','min:1'],
            'modules.*'             => [Rule::exists('modules','id')->where('status', 1)],
            'stagiaires'            => ['nullable','array'],
            'stagiaires.*.email'    => ['nullable','email','distinct'],
            'stagiaires.*.prenom'   => ['nullable','string','max:255'],
            'stagiaires.*.nom'      => ['nullable','string','max:255'],
        ]);

        // Vérifie l’appartenance
        $group = Group::where('id', $id)
            ->where('instructor_id', auth()->id())
            ->firstOrFail();

        // MAJ groupe
        $group->update([
            'name'        => $request->nom,
            'description' => $request->description,
        ]);

        // MAJ modules
        $group->modules()->sync($request->modules);

        // Ajout éventuel de nouveaux stagiaires
        if ($request->filled('stagiaires')) {
            foreach ($request->stagiaires as $s) {
                if (empty($s['email']) && empty($s['prenom']) && empty($s['nom'])) {
                    continue;
                }

                $user = User::where('email', $s['email'])->first();

                if (!$user) {
                    $user = new User();
                    $user->prenom       = $s['prenom'] ?? null;
                    $user->name         = $s['nom'] ?? null;
                    $user->email        = $s['email'] ?? null;
                    $user->password     = Hash::make(Str::random(12)); // temp
                    $user->role         = 'stagiaire';
                    $user->formateur_id = auth()->id();
                    $user->code_acces   = CodeGeneratorService::generateUniqueAccessCode();
                    $user->save();
                }

                $group->students()->syncWithoutDetaching([
                    $user->id => ['role_in_group' => 'stagiaire']
                ]);
            }
        }

        return redirect()
            ->route('formateur.groupes.index')
            ->with('success', 'Groupe modifié avec succès.');
    }

    /**
     * Suppression d’un groupe.
     */
    public function destroy($id)
    {
        $group = Group::where('id', $id)
            ->where('instructor_id', auth()->id())
            ->firstOrFail();

        // suppression image éventuelle
        if (!empty($group->groupe_image) && Storage::disk('public')->exists($group->groupe_image)) {
            Storage::disk('public')->delete($group->groupe_image);
        }

        $group->students()->detach();
        $group->modules()->detach();
        $group->delete();

        return redirect()
            ->route('formateur.groupes.index')
            ->with('success', 'Groupe supprimé avec succès.');
    }
}
