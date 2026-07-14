<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleSection;
use Illuminate\View\View;

class OutilsQuizQuestionsController extends Controller
{
    public function index(): View
    {
        $formateurId = (int) auth()->id();

        $toolModules = Module::query()
            ->authoredByTrainer($formateurId)
            ->with(['sections' => function ($query) {
                $query->select('id', 'module_id', 'section_title')
                    ->with(['lectures' => function ($lectureQuery) {
                        $lectureQuery
                            ->select('id', 'module_id', 'section_id', 'lecture_title', 'position')
                            ->withCount('quizQuestions')
                            ->orderBy('position')
                            ->orderBy('id');
                    }]);
            }])
            ->orderBy('module_title')
            ->get(['id', 'module_name', 'module_title'])
            ->map(function (Module $module) {
                $lectures = $module->sections->flatMap(function (ModuleSection $section) use ($module) {
                    return $section->lectures->map(fn ($lecture) => [
                        'id' => (int) $lecture->id,
                        'label' => trim($section->section_title.' · '.$lecture->lecture_title),
                        'questions_count' => (int) $lecture->quiz_questions_count,
                        'manage_url' => route('formateur.modules.builder.quiz-questions.index', [
                            'module' => $module->id,
                            'lecture' => $lecture->id,
                        ]),
                    ]);
                })->values();

                if ($lectures->isEmpty()) {
                    return null;
                }

                return [
                    'id' => (int) $module->id,
                    'title' => (string) ($module->module_title ?: $module->module_name ?: 'Module'),
                    'lectures_count' => $lectures->count(),
                    'questions_count' => (int) $lectures->sum('questions_count'),
                    'manage_url' => route('formateur.modules.builder.quiz-questions.index', $module->id),
                    'lectures' => $lectures,
                ];
            })
            ->filter()
            ->values();

        return view('formateur.outils.quiz_questions_index', compact('toolModules'));
    }
}
