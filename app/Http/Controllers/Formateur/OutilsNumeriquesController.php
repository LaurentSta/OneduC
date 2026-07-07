<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Module;
use App\Models\PollSession;
use App\Models\QuestionWall;
use App\Models\ScaleSession;
use App\Models\Seance;
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

        $recentScales = ScaleSession::query()
            ->where('formateur_id', $formateurId)
            ->with('group:id,name')
            ->withCount('responses')
            ->latest()
            ->limit(5)
            ->get();

        $recentModules = Module::query()
            ->authoredByTrainer($formateurId)
            ->withCount(['sections', 'groups'])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $openSeancesCount = Seance::whereIn('group_id', $groups->pluck('id'))->where('statut', 'ouverte')->count();

        return view('formateur.outils.index', compact('recentWordclouds', 'groups', 'recentQuestionWalls', 'recentPolls', 'recentScales', 'recentModules', 'openSeancesCount'));
    }
}
