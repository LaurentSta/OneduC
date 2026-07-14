<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\LiveQuizSession;
use App\Models\Module;
use App\Models\ModuleSection;
use Illuminate\View\View;

class OutilsLiveQuizController extends Controller
{
    public function index(): View
    {
        $formateurId = (int) auth()->id();

        $toolModules = Module::query()
            ->where(function ($q) use ($formateurId) {
                $q->authoredByTrainer($formateurId)
                    ->orWhereHas('groups', fn ($gq) => $gq->accessibleByTrainer($formateurId));
            })
            ->with([
                'sections' => function ($query) {
                    $query->orderBy('id')
                        ->select('id', 'module_id', 'section_title')
                        ->with(['lectures' => function ($lectureQuery) {
                            $lectureQuery
                                ->select('id', 'module_id', 'section_id', 'lecture_title', 'position')
                                ->withCount(['quizQuestions as active_questions_count' => fn ($q) => $q->where('is_active', true)])
                                ->orderBy('position')
                                ->orderBy('id');
                        }]);
                },
                'groups' => function ($query) use ($formateurId) {
                    $query->accessibleByTrainer($formateurId)
                        ->orderBy('groups.name')
                        ->select('groups.id', 'groups.name');
                },
            ])
            ->orderBy('module_title')
            ->get(['id', 'module_name', 'module_title', 'formateur_id', 'is_trainer_authored'])
            ->map(function (Module $module) use ($formateurId) {
                $canManage = (bool) $module->is_trainer_authored && (int) $module->formateur_id === $formateurId;

                $lectures = $module->sections->flatMap(function (ModuleSection $section) use ($canManage, $module) {
                    return $section->lectures->map(fn ($lecture) => [
                        'id' => (int) $lecture->id,
                        'section_id' => (int) $lecture->section_id,
                        'label' => trim($section->section_title.' · '.$lecture->lecture_title),
                        'questions_count' => (int) $lecture->active_questions_count,
                        'manage_url' => $canManage
                            ? route('formateur.modules.builder.quiz-questions.index', ['module' => $module->id, 'lecture' => $lecture->id])
                            : null,
                    ]);
                })->values();

                if ($lectures->isEmpty()) {
                    return null;
                }

                return [
                    'id' => (int) $module->id,
                    'title' => (string) ($module->module_title ?: $module->module_name ?: 'Module'),
                    'lectures' => $lectures,
                    'groups' => $module->groups->map(fn ($group) => [
                        'id' => (int) $group->id,
                        'name' => (string) $group->name,
                    ])->values(),
                ];
            })
            ->filter()
            ->values();

        $sessions = LiveQuizSession::where('formateur_id', $formateurId)
            ->with(['group', 'lecture'])
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('formateur.outils.quiz_index', compact('toolModules', 'sessions'));
    }
}
