<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\WordCloud;
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
        $formateurId = (int) auth()->id();

        $wordClouds = WordCloud::query()
            ->whereHas('group', fn ($q) => $q->where('instructor_id', $formateurId))
            ->with('group')
            ->latest()
            ->get();

        $groups = Group::query()
            ->where('instructor_id', $formateurId)
            ->orderBy('name')
            ->get();

        return view('formateur.outils.wordcloud_index', compact('wordClouds', 'groups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $formateurId = (int) auth()->id();

        $data = $request->validate([
            'group_id'    => ['required', 'exists:groups,id'],
            'title'       => ['required', 'string', 'max:255'],
            'questions'   => ['required', 'array', 'min:1', 'max:10'],
            'questions.*' => ['required', 'string', 'max:500'],
        ]);

        $group = Group::query()
            ->where('id', (int) $data['group_id'])
            ->where('instructor_id', $formateurId)
            ->firstOrFail();

        $wordCloud = WordCloud::query()->create([
            'group_id'    => $group->id,
            'module_id'   => null,
            'title'       => (string) $data['title'],
            'questions'   => $data['questions'],
            'question'    => $data['questions'][0], // rétro-compatibilité
            'access_code' => $this->generateCode(),
            'is_active'   => true,
            'opened_at'   => now(),
            'closed_at'   => null,
        ]);

        return redirect()
            ->route('formateur.nuages.live', $wordCloud)
            ->with('success', 'Nuage de mots lancé.');
    }

    public function live(WordCloud $wordCloud): View
    {
        $wordCloud->load(['group', 'module']);
        $this->assertOwnership($wordCloud);

        return view('formateur.wordcloud.live', [
            'wordCloud' => $wordCloud,
            'joinUrl'   => route('wordcloud.join.code', ['code' => $wordCloud->access_code]),
        ]);
    }

    public function liveData(WordCloud $wordCloud, Request $request): JsonResponse
    {
        $this->assertOwnership($wordCloud);

        $qi = (int) $request->query('q', 0);

        $words = $wordCloud->entries()
            ->where('question_index', $qi)
            ->select('normalized_answer as word', DB::raw('count(*) as score'))
            ->groupBy('normalized_answer')
            ->orderByDesc('score')
            ->orderBy('normalized_answer')
            ->limit(100)
            ->get();

        $respondents = $wordCloud->entries()
            ->where('question_index', $qi)
            ->distinct('user_id')
            ->count('user_id');

        return response()->json([
            'active'        => $wordCloud->is_active,
            'total_entries' => $words->sum('score'),
            'respondents'   => $respondents,
            'words'         => $words,
            'updated_at'    => now()->toIso8601String(),
        ]);
    }

    private function assertOwnership(WordCloud $wordCloud): void
    {
        abort_unless(
            (int) ($wordCloud->group?->instructor_id ?? 0) === (int) auth()->id(),
            403
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
