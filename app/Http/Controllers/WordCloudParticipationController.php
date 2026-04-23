<?php

namespace App\Http\Controllers;

use App\Models\WordCloud;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WordCloudParticipationController extends Controller
{
    private const STOP_WORDS = [
        'le', 'la', 'les', 'de', 'des', 'du', 'un', 'une', 'et', 'ou', 'en', 'a', 'au', 'aux', 'pour', 'sur',
        'dans', 'avec', 'sans', 'par', 'ce', 'cet', 'cette', 'ces', 'que', 'qui', 'quoi', 'donc', 'ne', 'pas',
    ];

    public function home(): View
    {
        return view('wordcloud.join');
    }

    public function joinByCode(string $code): View
    {
        $wordCloud = WordCloud::query()
            ->where('access_code', strtoupper($code))
            ->firstOrFail();

        $questions = $wordCloud->questions_array;

        return view('wordcloud.answer', compact('wordCloud', 'questions'));
    }

    public function submit(Request $request, string $code): RedirectResponse
    {
        $wordCloud = WordCloud::query()
            ->where('access_code', strtoupper($code))
            ->firstOrFail();

        if (!$wordCloud->is_active) {
            return back()->withErrors(['answer' => 'Ce nuage est fermé pour le moment.']);
        }

        $questions = $wordCloud->questions_array;

        $data = $request->validate([
            'answer'         => ['required', 'string', 'max:150'],
            'question_index' => ['required', 'integer', 'min:0', 'max:' . max(0, count($questions) - 1)],
        ]);

        $normalized = $this->normalizeAnswer($data['answer']);

        if ($normalized === null) {
            return back()->withErrors(['answer' => 'Réponse invalide. Utilise 1 à 3 mots utiles.'])->withInput();
        }

        $wordCloud->entries()->create([
            'user_id'           => auth()->id(),
            'question_index'    => $data['question_index'],
            'answer'            => trim($data['answer']),
            'normalized_answer' => $normalized,
        ]);

        return back()->with('success', true)->with('answered_qi', $data['question_index']);
    }

    public function liveData(string $code, Request $request): JsonResponse
    {
        $wordCloud = WordCloud::query()
            ->where('access_code', strtoupper($code))
            ->firstOrFail();

        $qi = (int) $request->query('q', 0);

        $words = $wordCloud->entries()
            ->where('question_index', $qi)
            ->select('normalized_answer as word', DB::raw('count(*) as score'))
            ->groupBy('normalized_answer')
            ->orderByDesc('score')
            ->limit(100)
            ->get();

        return response()->json([
            'words' => $words->values(),
        ]);
    }

    public function resolveCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:12'],
        ]);

        return redirect()->route('wordcloud.join.code', ['code' => strtoupper(trim($data['code']))]);
    }

    private function normalizeAnswer(string $answer): ?string
    {
        $clean = Str::of($answer)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9\s]/', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->value();

        if ($clean === '') {
            return null;
        }

        $words = array_values(array_filter(explode(' ', $clean)));
        if (count($words) < 1 || count($words) > 3) {
            return null;
        }

        if (count($words) === 1 && in_array($words[0], self::STOP_WORDS, true)) {
            return null;
        }

        return implode(' ', $words);
    }
}
