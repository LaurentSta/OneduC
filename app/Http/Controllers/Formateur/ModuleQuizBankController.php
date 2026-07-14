<?php

namespace App\Http\Controllers\Formateur;

use App\Domains\ModulesFormateur\Support\AccesModule;
use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\QuizQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleQuizBankController extends Controller
{
    public function __construct(private readonly AccesModule $access) {}

    public function index(Request $request, Module $module): View
    {
        $this->access->assertOwner($module);

        $module->load(['sections' => function ($query) {
            $query->orderBy('id')
                ->select('id', 'module_id', 'section_title')
                ->with(['lectures' => function ($lectureQuery) {
                    $lectureQuery
                        ->select('id', 'module_id', 'section_id', 'lecture_title', 'position')
                        ->withCount('quizQuestions')
                        ->orderBy('position')
                        ->orderBy('id');
                }]);
        }]);

        $lectureIds = $module->sections->flatMap(fn (ModuleSection $section) => $section->lectures)->pluck('id');

        $selectedLecture = null;
        $questions = collect();
        $otherLectures = collect();

        $requestedLectureId = (int) $request->query('lecture', 0);

        if ($requestedLectureId && $lectureIds->contains($requestedLectureId)) {
            $selectedLecture = ModuleLecture::find($requestedLectureId);
            $questions = QuizQuestion::where('lecture_id', $selectedLecture->id)
                ->with(['options' => fn ($q) => $q->orderBy('position')])
                ->withCount('options')
                ->orderBy('id')
                ->get();

            $otherLectures = $module->sections
                ->flatMap(fn (ModuleSection $section) => $section->lectures->map(fn ($l) => [
                    'id' => (int) $l->id,
                    'label' => $l->lecture_title,
                ]))
                ->reject(fn ($l) => $l['id'] === $selectedLecture->id)
                ->values();
        }

        return view('formateur.modules-builder.quiz-questions.module_index', [
            'module' => $module,
            'selectedLecture' => $selectedLecture,
            'questions' => $questions,
            'otherLectures' => $otherLectures,
        ]);
    }

    public function move(Request $request, QuizQuestion $question): RedirectResponse
    {
        $lecture = $question->lecture;
        $this->access->assertOwner($lecture->module);

        $data = $request->validate([
            'lecture_id' => ['required', 'integer', 'exists:module_lectures,id'],
        ]);

        $targetLecture = ModuleLecture::where('id', $data['lecture_id'])
            ->where('module_id', $lecture->module_id)
            ->firstOrFail();

        $question->update([
            'lecture_id' => $targetLecture->id,
        ]);

        return redirect()
            ->route('formateur.modules.builder.quiz-questions.index', ['module' => $lecture->module_id, 'lecture' => $targetLecture->id])
            ->with('success', 'Question déplacée vers "'.$targetLecture->lecture_title.'".');
    }
}
