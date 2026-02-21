<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Competency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
        $validated = $this->validateBadgePayload($request);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('badges', 'public');
        }

        $badge = Badge::create([
            'label' => $validated['label'],
            'is_active' => ($request->input('is_active', '0') === '1'),
            'image_path' => $imagePath,
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

        $validated = $this->validateBadgePayload($request);

        $updates = [
            'label' => $validated['label'],
            'is_active' => ($request->input('is_active', '0') === '1'),
        ];

        if ($request->hasFile('image')) {
            if (!empty($badge->image_path)) {
                Storage::disk('public')->delete($badge->image_path);
            }
            $updates['image_path'] = $request->file('image')->store('badges', 'public');
        } elseif ($request->boolean('remove_image')) {
            if (!empty($badge->image_path)) {
                Storage::disk('public')->delete($badge->image_path);
            }
            $updates['image_path'] = null;
        }

        $badge->update($updates);

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

        if (!empty($badge->image_path)) {
            Storage::disk('public')->delete($badge->image_path);
        }

        $badge->delete();

        return back()->with('success', 'Badge supprimé.');
    }

    private function validateBadgePayload(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'is_active' => 'nullable|in:0,1',
            'competency_ids' => 'nullable|array',
            'competency_ids.*' => 'integer|exists:competencies,id',
            'image' => 'nullable|file|mimes:svg|max:1024',
            'remove_image' => 'nullable|boolean',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!$request->hasFile('image')) {
                return;
            }

            if (!$this->isValidSvg($request->file('image')->getRealPath())) {
                $validator->errors()->add('image', "Le fichier doit être un SVG valide.");
            }
        });

        return $validator->validate();
    }

    private function isValidSvg(string $filePath): bool
    {
        $contents = @file_get_contents($filePath);
        if ($contents === false || stripos($contents, '<svg') === false) {
            return false;
        }

        $dom = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($contents);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            return false;
        }

        $svg = $dom->documentElement;
        return $svg && strtolower($svg->tagName) === 'svg';
    }
}
