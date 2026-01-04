<?php
// /home/laurents/Oneduc_Dev/app/Http/Controllers/Backend/SkillReferentialController.php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SkillReferential;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SkillReferentialController extends Controller
{
    /**
     * Liste des référentiels (admin).
     */
    public function index()
    {
        $referentiels = SkillReferential::query()
            ->withCount(['domains', 'skills'])
            ->orderBy('name')
            ->get();

        return view('admin.backend.referentiels.index', compact('referentiels'));
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        return view('admin.backend.referentiels.create');
    }

    /**
     * Enregistrement d’un référentiel.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'code'        => ['nullable', 'string', 'max:50', 'unique:skill_referentials,code'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status'      => ['nullable', 'boolean'],
        ]);

        $code = $validated['code'] ?? Str::upper(Str::slug($validated['name'], '_'));

        // Garantir l’unicité si code généré automatiquement
        if (SkillReferential::where('code', $code)->exists()) {
            $base = $code;
            $i = 2;
            while (SkillReferential::where('code', $base . '_' . $i)->exists()) {
                $i++;
            }
            $code = $base . '_' . $i;
        }

        SkillReferential::create([
            'name'        => $validated['name'],
            'code'        => $code,
            'description' => $validated['description'] ?? null,
            'status'      => (bool)($validated['status'] ?? true),
        ]);

        return redirect()
            ->route('admin.referentiels.index')
            ->with('success', 'Référentiel créé avec succès.');
    }

    /**
     * Formulaire d’édition.
     * Utilise le binding implicite : /admin/referentiels/{referentiel}/edit
     */
    public function edit(SkillReferential $referentiel)
    {
        return view('admin.backend.referentiels.edit', compact('referentiel'));
    }

    /**
     * Mise à jour.
     */
    public function update(Request $request, SkillReferential $referentiel)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'code'        => [
                'required',
                'string',
                'max:50',
                Rule::unique('skill_referentials', 'code')->ignore($referentiel->id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'status'      => ['nullable', 'boolean'],
        ]);

        $referentiel->update([
            'name'        => $validated['name'],
            'code'        => $validated['code'],
            'description' => $validated['description'] ?? null,
            'status'      => (bool)($validated['status'] ?? false),
        ]);

        return redirect()
            ->route('admin.referentiels.index')
            ->with('success', 'Référentiel mis à jour.');
    }

    /**
     * Suppression logique (SoftDeletes).
     */
    public function destroy(SkillReferential $referentiel)
    {
        $referentiel->delete();

        return redirect()
            ->route('admin.referentiels.index')
            ->with('success', 'Référentiel supprimé.');
    }

    /**
     * Optionnel : activer/désactiver depuis la liste.
     * (À utiliser si tu ajoutes une route PATCH dédiée.)
     */
    public function toggleStatus(SkillReferential $referentiel)
    {
        $referentiel->update([
            'status' => ! (bool) $referentiel->status,
        ]);

        return redirect()
            ->route('admin.referentiels.index')
            ->with('success', 'Statut du référentiel mis à jour.');
    }
}
