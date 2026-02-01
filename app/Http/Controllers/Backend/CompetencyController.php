<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Competency;
use Illuminate\Http\Request;

class CompetencyController extends Controller
{
    public function index()
    {
        $competencies = Competency::query()
            ->withCount([
                'objectives as objectives_count',
                'badges as badges_count',
            ])
            ->with([
                // Liste courte (2–3) pour affichage direct
                'objectives' => function ($q) {
                    $q->select('lecture_objectives.id', 'lecture_objectives.title')
                    ->orderBy('lecture_objectives.id', 'desc')
                    ->limit(3);
                },
                'badges' => function ($q) {
                    $q->select('badges.id', 'badges.label')
                    ->orderBy('badges.id', 'desc')
                    ->limit(3);
                },

            ])
            ->orderBy('code')
            ->paginate(15);

        return view('admin.backend.competencies.index', compact('competencies'));
    }



    public function store(Request $request)
    {
        $data = $request->validate([
            'code'        => ['nullable', 'string', 'max:80', 'unique:competencies,code'],
            'label'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Competency::create([
            'code'        => $data['code'] ?: null,
            'label'       => $data['label'],
            'description' => $data['description'] ?? null,
            'is_active'   => 1,
        ]);

        return back()->with('success', 'Compétence ajoutée.');
    }

    public function toggle(Competency $competency)
    {
        $competency->update(['is_active' => $competency->is_active ? 0 : 1]);

        return back()->with('success', $competency->is_active ? 'Compétence activée.' : 'Compétence désactivée.');
    }
    
    public function destroy(Competency $competency)
{
    $usedInObjectives = $competency->objectives()->exists();

    $usedInBadges = $competency->badges()
        ->where('badges.is_active', true)
        ->exists();

    if ($usedInObjectives || $usedInBadges) {
        return back()->withErrors(
            "Suppression impossible : cette compétence est utilisée par un objectif pédagogique ou un badge actif."
        );
    }

    // Nettoyage des pivots (bonne pratique)
    $competency->badges()->detach();
    $competency->objectives()->detach();

    $competency->delete();

    return back()->with('success', 'Compétence supprimée.');
}




}
