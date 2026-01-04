<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SkillDomain;
use App\Models\SkillReferential;
use Illuminate\Http\Request;

class SkillDomainController extends Controller
{
    public function index(SkillReferential $referentiel)
    {
        $domains = SkillDomain::where('skill_referential_id', $referentiel->id)
            ->orderBy('position')
            ->latest('id')
            ->get();

        return view('admin.backend.referentiels.domains.index', compact('referentiel', 'domains'));
    }

    public function create(SkillReferential $referentiel)
    {
        return view('admin.backend.referentiels.domains.create', compact('referentiel'));
    }

    public function store(Request $request, SkillReferential $referentiel)
    {
        $validated = $request->validate([
            'name'        => ['required','string','max:150'],
            'description' => ['nullable','string','max:5000'],
            'position'    => ['nullable','integer','min:0'],
            'status'      => ['nullable','boolean'],
        ]);

        SkillDomain::create([
            'skill_referential_id' => $referentiel->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'position' => (int)($validated['position'] ?? 0),
            'status' => (bool)($validated['status'] ?? true),
        ]);

        return redirect()
            ->route('admin.referentiels.domains.index', $referentiel)
            ->with('success', 'Domaine créé.');
    }

    public function edit(SkillReferential $referentiel, SkillDomain $domain)
    {
        abort_unless($domain->skill_referential_id === $referentiel->id, 404);
        return view('admin.backend.referentiels.domains.edit', compact('referentiel', 'domain'));
    }

    public function update(Request $request, SkillReferential $referentiel, SkillDomain $domain)
    {
        abort_unless($domain->skill_referential_id === $referentiel->id, 404);

        $validated = $request->validate([
            'name'        => ['required','string','max:150'],
            'description' => ['nullable','string','max:5000'],
            'position'    => ['nullable','integer','min:0'],
            'status'      => ['nullable','boolean'],
        ]);

        $domain->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'position' => (int)($validated['position'] ?? 0),
            'status' => (bool)($validated['status'] ?? false),
        ]);

        return redirect()
            ->route('admin.referentiels.domains.index', $referentiel)
            ->with('success', 'Domaine mis à jour.');
    }

    public function destroy(SkillReferential $referentiel, SkillDomain $domain)
    {
        abort_unless($domain->skill_referential_id === $referentiel->id, 404);

        $domain->delete();

        return redirect()
            ->route('admin.referentiels.domains.index', $referentiel)
            ->with('success', 'Domaine supprimé.');
    }
}
