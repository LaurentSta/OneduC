<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\SkillDomain;
use App\Models\SkillReferential;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SkillController extends Controller
{
    public function index(Request $request, SkillReferential $referentiel)
    {
        $domainId = $request->get('domain');

        $skills = $referentiel->skills()
            ->when($domainId === 'none', fn ($q) => $q->whereNull('skill_domain_id'))
            ->when(is_numeric($domainId), fn ($q) => $q->where('skill_domain_id', (int) $domainId))
            ->with('domain')
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $domains = SkillDomain::where('skill_referential_id', $referentiel->id)
            ->orderBy('position')
            ->get();

        return view(
            'admin.backend.referentiels.skills.index',
            compact('referentiel', 'skills', 'domains', 'domainId')
        );
    }

    public function create(SkillReferential $referentiel)
    {
        $domains = SkillDomain::where('skill_referential_id', $referentiel->id)
            ->orderBy('position')
            ->get();

        return view(
            'admin.backend.referentiels.skills.create',
            compact('referentiel', 'domains')
        );
    }

    public function store(Request $request, SkillReferential $referentiel)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:5000'],
            'skill_domain_id' => [
                'nullable',
                Rule::exists('skill_domains', 'id')
                    ->where('skill_referential_id', $referentiel->id),
            ],
            'position' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'boolean'],
        ]);

        // Génération automatique du code si absent
        $code = $validated['code']
            ?? Str::upper(Str::slug($validated['name'], '_'));

        // Unicité du code dans le référentiel
        if (
            Skill::where('skill_referential_id', $referentiel->id)
                ->where('code', $code)
                ->exists()
        ) {
            $base = $code;
            $i = 2;

            while (
                Skill::where('skill_referential_id', $referentiel->id)
                    ->where('code', "{$base}_{$i}")
                    ->exists()
            ) {
                $i++;
            }

            $code = "{$base}_{$i}";
        }

        Skill::create([
            'skill_referential_id' => $referentiel->id,
            'skill_domain_id' => $validated['skill_domain_id'] ?? null,
            'name' => $validated['name'],
            'code' => $code,
            'description' => $validated['description'] ?? null,
            'position' => (int) ($validated['position'] ?? 0),
            'status' => array_key_exists('status', $validated)
                ? (bool) $validated['status']
                : true,
        ]);

        return redirect()
            ->route('admin.referentiels.skills.index', $referentiel)
            ->with('success', 'Compétence créée.');
    }

    public function edit(SkillReferential $referentiel, Skill $skill)
    {
        $this->ensureSkillBelongsToReferential($referentiel, $skill);

        $domains = SkillDomain::where('skill_referential_id', $referentiel->id)
            ->orderBy('position')
            ->get();

        return view(
            'admin.backend.referentiels.skills.edit',
            compact('referentiel', 'skill', 'domains')
        );
    }

    public function update(Request $request, SkillReferential $referentiel, Skill $skill)
    {
        $this->ensureSkillBelongsToReferential($referentiel, $skill);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('skills', 'code')
                    ->where('skill_referential_id', $referentiel->id)
                    ->ignore($skill->id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'skill_domain_id' => [
                'nullable',
                Rule::exists('skill_domains', 'id')
                    ->where('skill_referential_id', $referentiel->id),
            ],
            'position' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'boolean'],
        ]);

        $skill->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'skill_domain_id' => $validated['skill_domain_id'] ?? null,
            'position' => (int) ($validated['position'] ?? 0),
            'status' => array_key_exists('status', $validated)
                ? (bool) $validated['status']
                : false,
        ]);

        return redirect()
            ->route('admin.referentiels.skills.index', $referentiel)
            ->with('success', 'Compétence mise à jour.');
    }

    public function destroy(SkillReferential $referentiel, Skill $skill)
    {
        $this->ensureSkillBelongsToReferential($referentiel, $skill);

        $skill->delete();

        return redirect()
            ->route('admin.referentiels.skills.index', $referentiel)
            ->with('success', 'Compétence supprimée.');
    }

    private function ensureSkillBelongsToReferential(
        SkillReferential $referentiel,
        Skill $skill
    ): void {
        abort_unless(
            $skill->skill_referential_id === $referentiel->id,
            404
        );
    }
}
