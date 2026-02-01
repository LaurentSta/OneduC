<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Competency;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function index()
    {
        $badges = Badge::query()
            ->withCount([
                'competencies as competencies_count',
            ])
            ->with([
                'competencies' => function ($q) {
                    $q->select('competencies.id', 'competencies.label')
                    ->orderBy('competencies.label')
                    ->limit(3);
                },
            ])
            ->orderBy('label')
            ->paginate(15);

        return view('admin.backend.badges.index', compact('badges'));
    }


    public function create()
    {
        $competencies = Competency::select('id','code','label')
            ->where('is_active', 1)
            ->orderBy('label')
            ->get();

        return view('admin.backend.badges.create', compact('competencies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'is_active' => 'nullable|in:0,1',
            'competency_ids' => 'nullable|array',
            'competency_ids.*' => 'integer|exists:competencies,id',
        ]);

        $badge = Badge::create([
            'label' => $validated['label'],
            'is_active' => ($request->input('is_active', '0') === '1'),
        ]);

        $ids = $request->input('competency_ids', []);
        $syncData = [];
        foreach (array_values(array_unique($ids)) as $pos => $cid) {
            $syncData[(int)$cid] = ['position' => $pos + 1];
        }
        $badge->competencies()->sync($syncData);

        return redirect()->route('admin.badges.edit', $badge)->with('success', 'Badge créé.');
    }

    
    public function edit($id)
    {
        $badge = Badge::with('competencies')->findOrFail($id);

        $competencies = Competency::select('id', 'code', 'label')
            ->where('is_active', 1)
            ->orderBy('label')
            ->get();

        return view('admin.backend.badges.edit', compact('badge', 'competencies'));
    }

    public function update(Request $request, $id)
    {
        $badge = Badge::findOrFail($id);

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'is_active' => 'nullable|in:0,1',
            'competency_ids' => 'nullable|array',
            'competency_ids.*' => 'integer|exists:competencies,id',
        ]);

        $badge->update([
            'label' => $validated['label'],
            'is_active' => ($request->input('is_active', '0') === '1'),
        ]);

        // Pivot badge_competency(position)
        $ids = $request->input('competency_ids', []);
        $syncData = [];

        foreach (array_values(array_unique($ids)) as $pos => $cid) {
            $syncData[(int) $cid] = ['position' => $pos + 1];
        }

        $badge->competencies()->sync($syncData);

        return back()->with('success', 'Badge mis à jour.');
    }
    public function destroy(Badge $badge)
{
    if ($badge->competencies()->exists()) {
        return back()->withErrors(
            "Suppression impossible : ce badge est associé à une ou plusieurs compétences."
        );
    }

    $badge->delete();

    return back()->with('success', 'Badge supprimé.');
}
}
