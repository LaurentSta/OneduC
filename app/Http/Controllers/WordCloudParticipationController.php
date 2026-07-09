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
        $activeQuestionIndex = $wordCloud->active_question_index;
        $activeQuestion = $wordCloud->active_question;
        $questionCount = count($questions);

        return view('wordcloud.answer', compact(
            'wordCloud',
            'questions',
            'activeQuestionIndex',
            'activeQuestion',
            'questionCount'
        ));
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
        $activeQuestionIndex = $wordCloud->active_question_index;

        $data = $request->validate([
            'answer'         => ['required', 'string', 'max:150'],
            'question_index' => ['required', 'integer', 'min:0', 'max:' . max(0, count($questions) - 1)],
        ]);

        if ((int) $data['question_index'] !== $activeQuestionIndex) {
            return back()
                ->withErrors(['answer' => 'La question active a changé. Réessayez sur la question affichée.'])
                ->withInput();
        }

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

    public function state(string $code): JsonResponse
    {
        $wordCloud = WordCloud::query()
            ->where('access_code', strtoupper($code))
            ->firstOrFail();

        return response()->json([
            'active' => $wordCloud->is_active,
            'current_question_index' => $wordCloud->active_question_index,
            'question_count' => count($wordCloud->questions_array),
            'question' => $wordCloud->active_question,
            'updated_at' => $wordCloud->updated_at?->toIso8601String(),
        ]);
    }

    public function liveData(string $code, Request $request): JsonResponse
    {
        $wordCloud = WordCloud::query()
            ->where('access_code', strtoupper($code))
            ->firstOrFail();

        $qi = (int) $request->query('q', $wordCloud->active_question_index);
        $questionIndex = min(max($qi, 0), max(0, count($wordCloud->questions_array) - 1));

        $words = $wordCloud->entries()
            ->where('question_index', $questionIndex)
            ->select('normalized_answer as word', DB::raw('count(*) as score'))
            ->groupBy('normalized_answer')
            ->orderByDesc('score')
            ->limit(100)
            ->get();

        return response()->json([
            'active' => $wordCloud->is_active,
            'current_question_index' => $wordCloud->active_question_index,
            'question_index' => $questionIndex,
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
