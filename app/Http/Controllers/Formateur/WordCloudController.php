<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Module;
use App\Models\WordCloud;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WordCloudController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'module_id' => ['required', 'exists:modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'question' => ['required', 'string', 'max:1000'],
        ]);

        $group = Group::query()
            ->where('id', (int) $data['group_id'])
            ->where('instructor_id', (int) auth()->id())
            ->firstOrFail();

        $module = Module::query()
            ->where('id', (int) $data['module_id'])
            ->where('formateur_id', (int) auth()->id())
            ->firstOrFail();

        abort_unless($group->modules()->where('modules.id', $module->id)->exists(), 403);

        $wordCloud = WordCloud::query()->create([
            'group_id' => $group->id,
            'module_id' => $module->id,
            'title' => (string) $data['title'],
            'question' => (string) $data['question'],
            'access_code' => $this->generateCode(),
            'is_active' => true,
            'opened_at' => now(),
            'closed_at' => null,
        ]);

        return redirect()
            ->route('formateur.wordclouds.live', ['wordCloud' => $wordCloud->id])
            ->with('success', 'Nuage de mots lance.');
    }

    public function live(WordCloud $wordCloud): View
    {
        $wordCloud->load(['group', 'module']);
        $this->assertOwnership($wordCloud);

        return view('formateur.wordcloud.live', [
            'wordCloud' => $wordCloud,
            'joinUrl' => route('wordcloud.join.code', ['code' => $wordCloud->access_code]),
        ]);
    }

    public function liveData(WordCloud $wordCloud): JsonResponse
    {
        $this->assertOwnership($wordCloud);

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

    private function assertOwnership(WordCloud $wordCloud): void
    {
        $group = $wordCloud->group;
        $module = $wordCloud->module;

        abort_unless(
            ((int) ($group->instructor_id ?? 0) === (int) auth()->id())
            || ((int) ($module->formateur_id ?? 0) === (int) auth()->id()),
            404
        );
    }

    private function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (WordCloud::query()->where('access_code', $code)->exists());

        return $code;
    }
}
