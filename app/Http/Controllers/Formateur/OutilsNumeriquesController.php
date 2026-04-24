<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\PollSession;
use App\Models\QuestionWall;
use App\Models\WordCloud;
use Illuminate\View\View;

class OutilsNumeriquesController extends Controller
{
    public function index(): View
    {
        $formateurId = (int) auth()->id();

        $recentWordclouds = WordCloud::query()
            ->whereHas('group', fn ($q) => $q->where('instructor_id', $formateurId))
            ->with(['group', 'module'])
            ->latest()
            ->limit(5)
            ->get();

        $groups = Group::query()
            ->where('instructor_id', $formateurId)
            ->withCount('students')
            ->orderBy('name')
            ->get();

        $recentQuestionWalls = QuestionWall::query()
            ->where('formateur_id', $formateurId)
            ->with('group')
            ->withCount('questions')
            ->latest()
            ->limit(5)
            ->get();

        $recentPolls = PollSession::query()
            ->where('formateur_id', $formateurId)
            ->with('group:id,name')
            ->withCount('responses')
            ->latest()
            ->limit(5)
            ->get();

        return view('formateur.outils.index', compact('recentWordclouds', 'groups', 'recentQuestionWalls', 'recentPolls'));
    }
}
