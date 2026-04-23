<?php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Controller;
use App\Models\FormateurParcoursItem;
use App\Models\Group;
use App\Models\WordCloudEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ParcoursWordCloudController extends Controller
{
    private const STOP_WORDS = [
        'le', 'la', 'les', 'de', 'des', 'du', 'un', 'une', 'et', 'ou', 'en', 'a', 'au', 'aux', 'pour', 'sur',
        'dans', 'avec', 'sans', 'par', 'ce', 'cet', 'cette', 'ces', 'que', 'qui', 'quoi', 'donc', 'ne', 'pas',
    ];

    public function show(FormateurParcoursItem $item): View
    {
        abort_unless($item->type === 'wordcloud', 404);

        $user = auth()->user();

        // Vérifier que le stagiaire appartient à un groupe lié à ce parcours
        abort_unless(
            Group::query()
                ->where('formateur_parcours_id', $item->formateur_parcours_id)
                ->whereHas('students', fn ($q) => $q->where('email', $user->email))
                ->exists(),
            403
        );

        $questions = $item->wc_questions ?? [];

        // Réponses existantes du stagiaire, indexées par question_index
        $myAnswers = WordCloudEntry::query()
            ->where('formateur_parcours_item_id', $item->id)
            ->where('user_id', $user->id)
            ->pluck('answer', 'question_index');

        return view('stagiaire.parcours_wordcloud', compact('item', 'questions', 'myAnswers'));
    }

    public function submit(Request $request, FormateurParcoursItem $item): RedirectResponse
    {
        abort_unless($item->type === 'wordcloud', 404);

        $user = auth()->user();

        abort_unless(
            Group::query()
                ->where('formateur_parcours_id', $item->formateur_parcours_id)
                ->whereHas('students', fn ($q) => $q->where('email', $user->email))
                ->exists(),
            403
        );

        $questions = $item->wc_questions ?? [];

        $data = $request->validate([
            'question_index' => ['required', 'integer', 'min:0', 'max:' . max(0, count($questions) - 1)],
            'answer'         => ['required', 'string', 'max:150'],
        ]);

        $normalized = $this->normalizeAnswer($data['answer']);

        if ($normalized === null) {
            return back()
                ->withErrors(['answer' => 'Réponse invalide. Utilise 1 à 3 mots (sans articles ni prépositions).'])
                ->withInput();
        }

        WordCloudEntry::updateOrCreate(
            [
                'formateur_parcours_item_id' => $item->id,
                'question_index'             => $data['question_index'],
                'user_id'                    => $user->id,
            ],
            [
                'word_cloud_id'    => null,
                'answer'           => trim($data['answer']),
                'normalized_answer' => $normalized,
            ]
        );

        return back()->with('success', 'Réponse enregistrée !');
    }

    public function liveData(FormateurParcoursItem $item, Request $request): JsonResponse
    {
        abort_unless($item->type === 'wordcloud', 404);

        $questionIndex = (int) $request->query('q', 0);

        $words = WordCloudEntry::query()
            ->where('formateur_parcours_item_id', $item->id)
            ->where('question_index', $questionIndex)
            ->selectRaw('normalized_answer as word, count(*) as score')
            ->groupBy('normalized_answer')
            ->orderByDesc('score')
            ->get();

        return response()->json([
            'total_entries' => $words->sum('score'),
            'words'         => $words->values(),
        ]);
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
