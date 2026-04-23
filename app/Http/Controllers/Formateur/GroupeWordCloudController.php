<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\FormateurParcoursItem;
use App\Models\Group;
use App\Models\WordCloudEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupeWordCloudController extends Controller
{
    public function live(Group $group, FormateurParcoursItem $item): View
    {
        abort_unless($item->type === 'wordcloud', 404);
        abort_unless($group->formateur_parcours_id === $item->formateur_parcours_id, 404);
        abort_unless($group->instructor_id === (int) auth()->id(), 403);

        $questions = $item->wc_questions ?? [];

        // Comptage des répondants par question
        $studentIds = $group->students()->pluck('users.id');

        $responseCounts = WordCloudEntry::query()
            ->where('formateur_parcours_item_id', $item->id)
            ->whereIn('user_id', $studentIds)
            ->selectRaw('question_index, count(distinct user_id) as respondents')
            ->groupBy('question_index')
            ->pluck('respondents', 'question_index');

        $totalStudents = $studentIds->count();

        return view('formateur.groupes.wordcloud_live', compact(
            'group', 'item', 'questions', 'responseCounts', 'totalStudents'
        ));
    }

    public function liveData(Group $group, FormateurParcoursItem $item, Request $request): JsonResponse
    {
        abort_unless($item->type === 'wordcloud', 404);
        abort_unless($group->formateur_parcours_id === $item->formateur_parcours_id, 404);
        abort_unless($group->instructor_id === (int) auth()->id(), 403);

        $questionIndex = (int) $request->query('q', 0);
        $studentIds    = $group->students()->pluck('users.id');

        $words = WordCloudEntry::query()
            ->where('formateur_parcours_item_id', $item->id)
            ->where('question_index', $questionIndex)
            ->whereIn('user_id', $studentIds)
            ->selectRaw('normalized_answer as word, count(*) as score')
            ->groupBy('normalized_answer')
            ->orderByDesc('score')
            ->get();

        $respondents = WordCloudEntry::query()
            ->where('formateur_parcours_item_id', $item->id)
            ->where('question_index', $questionIndex)
            ->whereIn('user_id', $studentIds)
            ->distinct('user_id')
            ->count('user_id');

        return response()->json([
            'total_entries' => $words->sum('score'),
            'respondents'   => $respondents,
            'words'         => $words->values(),
        ]);
    }
}
