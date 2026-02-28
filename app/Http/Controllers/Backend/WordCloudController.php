<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\WordCloud;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WordCloudController extends Controller
{
    public function index(): View
    {
        $wordClouds = WordCloud::with('module:id,module_name')
            ->withCount('entries')
            ->orderByDesc('id')
            ->paginate(50);

        return view('admin.backend.wordcloud.index', compact('wordClouds'));
    }

    public function create(): View
    {
        $modules = Module::query()
            ->orderBy('module_name')
            ->get(['id', 'module_name']);

        return view('admin.backend.wordcloud.create', compact('modules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'question' => ['required', 'string', 'max:1000'],
            'module_id' => ['nullable', 'exists:modules,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $wordCloud = WordCloud::create([
            ...$data,
            'access_code' => $this->generateCode(),
            'is_active' => (bool)($data['is_active'] ?? true),
            'opened_at' => ($data['is_active'] ?? true) ? now() : null,
        ]);

        return redirect()->route('admin.nuage.show', $wordCloud)
            ->with('success', 'Nuage créé avec succès.');
    }

    public function show(WordCloud $wordCloud): View
    {
        $wordCloud->loadCount('entries');

        $joinUrl = route('wordcloud.join.code', ['code' => $wordCloud->access_code]);

        return view('admin.backend.wordcloud.show', compact('wordCloud', 'joinUrl'));
    }

    public function edit(WordCloud $wordCloud): View
    {
        $modules = Module::query()
            ->orderBy('module_name')
            ->get(['id', 'module_name']);

        return view('admin.backend.wordcloud.edit', compact('wordCloud', 'modules'));
    }

    public function update(Request $request, WordCloud $wordCloud): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'question' => ['required', 'string', 'max:1000'],
            'module_id' => ['nullable', 'exists:modules,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $isActive = (bool)($data['is_active'] ?? false);

        $wordCloud->update([
            ...$data,
            'is_active' => $isActive,
            'opened_at' => $isActive ? ($wordCloud->opened_at ?: now()) : $wordCloud->opened_at,
            'closed_at' => $isActive ? null : Carbon::now(),
        ]);

        return redirect()->route('admin.nuage.index')
            ->with('success', 'Nuage mis à jour.');
    }

    public function destroy(WordCloud $wordCloud): RedirectResponse
    {
        $wordCloud->delete();

        return redirect()->route('admin.nuage.index')
            ->with('success', 'Nuage supprimé.');
    }

    public function live(WordCloud $wordCloud): View
    {
        return view('admin.backend.wordcloud.live', compact('wordCloud'));
    }

    public function liveData(WordCloud $wordCloud): JsonResponse
    {
        $words = $wordCloud->entries()
            ->select('normalized_answer as word', DB::raw('count(*) as score'))
            ->groupBy('normalized_answer')
            ->orderByDesc('score')
            ->orderBy('normalized_answer')
            ->limit(100)
            ->get();

        return response()->json([
            'active' => $wordCloud->is_active,
            'total_entries' => $wordCloud->entries()->count(),
            'words' => $words,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    private function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (WordCloud::where('access_code', $code)->exists());

        return $code;
    }
}
