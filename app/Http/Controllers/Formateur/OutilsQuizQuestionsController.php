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
            ->with([
                'sections' => function ($query) {
                    $query->orderBy('id')
                        ->select('id', 'module_id', 'section_title')
                        ->with(['lectures' => function ($lectureQuery) {
                            $lectureQuery
                                ->orderBy('position')
                                ->orderBy('id')
                                ->withCount('quizQuestions')
                                ->select('id', 'module_id', 'section_id', 'lecture_title', 'position', 'quiz_enabled');
                        }]);
                },
            ])
            ->orderBy('module_title')
            ->get(['id', 'module_name', 'module_title'])
            ->map(function (Module $module) {
                $lectures = $module->sections->flatMap(function (ModuleSection $section) {
                    return $section->lectures->map(fn ($lecture) => [
                        'id' => (int) $lecture->id,
                        'section_id' => (int) $lecture->section_id,
                        'label' => trim($section->section_title.' · '.$lecture->lecture_title),
                        'quiz_enabled' => (bool) $lecture->quiz_enabled,
                        'questions_count' => (int) $lecture->quiz_questions_count,
                        'manage_url' => route('formateur.modules.builder.lectures.quiz.questions.index', $lecture->id),
                    ]);
                })->values();

                if ($lectures->isEmpty()) {
                    return null;
                }

                return [
                    'id' => (int) $module->id,
                    'title' => (string) ($module->module_title ?: $module->module_name ?: 'Module'),
                    'lectures' => $lectures,
                ];
            })
            ->filter()
            ->values();

        return view('formateur.outils.quiz_questions_index', compact('toolModules'));
    }
}
