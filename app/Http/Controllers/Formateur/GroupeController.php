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
    // …validation…

    \DB::transaction(function () use ($request) {
        $group = Group::create([
            'name'          => $request->nom,
            'description'   => $request->description,
            'instructor_id' => auth()->id(),
        ]);

        foreach ($request->stagiaires as $s) {
            if (empty($s['email']) && empty($s['prenom']) && empty($s['nom'])) continue;

            $email = strtolower(trim($s['email']));
            $user  = User::withTrashed()->where('email', $email)->first();

            if ($user) {
                if ($user->trashed()) $user->restore();
                if (!$user->formateur_id) $user->formateur_id = auth()->id();
                $user->prenom = $user->prenom ?: $s['prenom'];
                $user->name   = $user->name   ?: $s['nom'];
                $user->save();
            } else {
                $user = User::create([
                    'prenom'       => $s['prenom'],
                    'name'         => $s['nom'],
                    'email'        => $email,
                    'password'     => \Hash::make($request->password),
                    'role'         => 'stagiaire',
                    'formateur_id' => auth()->id(),
                    'status'       => 1,
                    'code_acces'   => CodeGeneratorService::generateUniqueAccessCode(),
                ]);
            }

            $group->students()->syncWithoutDetaching([$user->id => ['role_in_group' => 'stagiaire']]);
        }

        $group->modules()->sync($request->modules);
    });

    return redirect()->route('formateur.groupes.index')->with('success', 'Groupe et stagiaires enregistrés avec succès.');
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
            'nom' => [
                'required',
                'string',
                'max:150',
                Rule::unique('groups', 'name')->ignore($id),
            ],
            'description' => ['nullable','string','max:2000'],

            'modules' => ['required','array','min:1'],
            'modules.*' => [Rule::exists('modules', 'id')->where('status', 1)],

            'stagiaires' => ['nullable','array'],
            'stagiaires.*.email' => ['nullable','email','distinct'],
            'stagiaires.*.prenom' => ['nullable','string','max:255'],
            'stagiaires.*.nom' => ['nullable','string','max:255'],

            'remove_students' => ['nullable','array'],
            'remove_students.*' => ['integer', Rule::exists('users', 'id')],
        ]);


        // Vérifie l’appartenance
        $group = Group::where('id', $id)
            ->where('instructor_id', auth()->id())
            ->with('students')
            ->firstOrFail();

        // MAJ groupe
        $group->update([
            'name'        => $request->nom,
            'description' => $request->description,
        ]);

        // MAJ modules
        $group->modules()->sync($request->modules);
        $removeIds = collect($request->input('remove_students', []))
            ->map(fn($v) => (int) $v)
            ->filter()
            ->unique();

        if ($removeIds->isNotEmpty()) {
            $currentIds = $group->students->pluck('id')->map(fn($v) => (int) $v);
            $safeDetach = $removeIds->intersect($currentIds);

            if ($safeDetach->isNotEmpty()) {
                $group->students()->detach($safeDetach->all());
            }
        }

        // Ajout éventuel de nouveaux stagiaires
        if ($request->filled('stagiaires')) {
            foreach ($request->stagiaires as $s) {
                if (empty($s['email']) && empty($s['prenom']) && empty($s['nom'])) {
                    continue;
                }

                $email = strtolower(trim($s['email'] ?? ''));
                if ($email === '') { continue; }

                $user = User::withTrashed()->where('email', $email)->first();

                if ($user) {
                    if ($user->trashed()) { $user->restore(); }
                    if (!$user->formateur_id) { $user->formateur_id = auth()->id(); }
                    $user->prenom = $user->prenom ?: ($s['prenom'] ?? null);
                    $user->name   = $user->name   ?: ($s['nom'] ?? null);
                    $user->save();
                } else {
                    $user = User::create([
                        'prenom'       => $s['prenom'] ?? null,
                        'name'         => $s['nom'] ?? null,
                        'email'        => $email,
                        'password'     => Hash::make(Str::random(12)),
                        'role'         => 'stagiaire',
                        'formateur_id' => auth()->id(),
                        'status'       => 1,
                        'code_acces'   => CodeGeneratorService::generateUniqueAccessCode(),
                    ]);
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
