<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupQuizSession;
use App\Models\QuizQuestionnaire;
use Illuminate\View\View;

class OutilsLiveQuizController extends Controller
{
    public function index(): View
    {
        $formateurId = (int) auth()->id();

        $groups = Group::query()
            ->where('instructor_id', $formateurId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $questionnaires = QuizQuestionnaire::query()
            ->where('formateur_id', $formateurId)
            ->with(['questions' => function ($query) {
                $query->with(['options' => fn ($q) => $q->orderBy('position')->orderBy('id')])
                    ->orderBy('id');
            }])
            ->orderBy('created_at')
            ->get();

        $sessions = GroupQuizSession::where('formateur_id', $formateurId)
            ->with('group')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('formateur.outils.quiz_index', compact('groups', 'questionnaires', 'sessions'));
    }
}
