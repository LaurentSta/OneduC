<?php

// /var/www/Oneduc_Prod/app/Http/Controllers/Backend/QuizQuestionController.php

namespace App\Http\Controllers\Backend;

use App\Domains\ModulesFormateur\Actions\CreerQuestionQuiz;
use App\Domains\ModulesFormateur\Actions\GenererQuestionsQuizIA;
use App\Domains\ModulesFormateur\Actions\ModifierQuestionQuiz;
use App\Domains\ModulesFormateur\Actions\SupprimerQuestionQuiz;
use App\Domains\ModulesFormateur\Support\AccesFormationCatalogue;
use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\QuizQuestion;
use App\Services\QuizQuestionBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class QuizQuestionController extends Controller
{
    public function __construct(
        private readonly QuizQuestionBuilder $builder,
        private readonly AccesFormationCatalogue $acces,
        private readonly CreerQuestionQuiz $creerQuestionQuiz,
        private readonly ModifierQuestionQuiz $modifierQuestionQuiz,
        private readonly SupprimerQuestionQuiz $supprimerQuestionQuiz,
        private readonly GenererQuestionsQuizIA $genererQuestionsQuizIA,
    ) {}

    /**
     * Liste des questions d’un quiz (par leçon).
     */
    public function index(ModuleLecture $lecture)
    {
        $this->acces->assertCatalogue($lecture->module);
        $questions = QuizQuestion::where('lecture_id', $lecture->id)
            ->with(['options' => fn ($q) => $q->orderBy('position')])
            ->withCount('options')
            ->orderBy('id')
            ->get();

        return view('admin.backend.quiz.questions.index', [
            'lecture' => $lecture,
            'questions' => $questions,
        ]);
    }

    /**
     * Formulaire de création d’une question.
     */
    public function create(ModuleLecture $lecture)
    {
        $this->acces->assertEditable($lecture->module);
        return view('admin.backend.quiz.questions.create', [
            'lecture' => $lecture,
        ]);
    }

    /**
     * Enregistrement d’une nouvelle question + ses options.
     */
    public function store(Request $request, ModuleLecture $lecture)
    {
        $this->acces->assertEditable($lecture->module);
        $data = $this->builder->validatePayload($request);

        $this->creerQuestionQuiz->execute($lecture, $data, $request);

        return redirect()
            ->route('admin.quiz.questions.index', $lecture)
            ->with('success', 'Question créée avec succès.');
    }

    /**
     * Formulaire d’édition d’une question.
     */
    public function edit(ModuleLecture $lecture, QuizQuestion $question)
    {
        $this->acces->assertEditable($lecture->module);
        abort_unless($question->lecture_id === $lecture->id, 404);

        $question->load(['options' => fn ($q) => $q->orderBy('position')]);

        return view('admin.backend.quiz.questions.edit', [
            'lecture' => $lecture,
            'question' => $question,
        ]);
    }

    /**
     * Mise à jour d’une question + options.
     */
    public function update(Request $request, ModuleLecture $lecture, QuizQuestion $question)
    {
        $this->acces->assertEditable($lecture->module);
        abort_unless($question->lecture_id === $lecture->id, 404);

        $data = $this->builder->validatePayload($request);

        $this->modifierQuestionQuiz->execute($question, $data, $request);

        return redirect()
            ->route('admin.quiz.questions.index', $lecture)
            ->with('success', 'Question mise à jour.');
    }

    /**
     * Suppression d’une question.
     */
    public function destroy(ModuleLecture $lecture, QuizQuestion $question)
    {
        $this->acces->assertEditable($lecture->module);
        abort_unless($question->lecture_id === $lecture->id, 404);

        $this->supprimerQuestionQuiz->execute($question);

        return redirect()
            ->route('admin.quiz.questions.index', $lecture)
            ->with('success', 'Question supprimée.');
    }

    /**
     * Import CSV en masse des questions d'une leçon.
     */
    public function importCsv(Request $request, ModuleLecture $lecture)
    {
        $this->acces->assertEditable($lecture->module);
        $validated = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $result = $this->builder->importCsv($lecture, $validated['csv_file'], (int) auth()->id());

        $redirect = redirect()->route('admin.quiz.questions.index', $lecture)
            ->with('success', "Import CSV terminé: {$result['created']} question(s) créée(s).");

        if (! empty($result['errors'])) {
            $bag = new MessageBag(['csv_import' => $result['errors']]);
            $redirect = $redirect
                ->with('import_report', [
                    'created' => $result['created'],
                    'errors_count' => count($result['errors']),
                ])
                ->withErrors($bag);
        } else {
            $redirect = $redirect
                ->with('import_report', [
                    'created' => $result['created'],
                    'errors_count' => 0,
                ]);
        }

        return $redirect;
    }

    /**
     * Télécharge un modèle CSV d'import.
     */
    public function downloadCsvTemplate(ModuleLecture $lecture)
    {
        $this->acces->assertEditable($lecture->module);

        return response($this->builder->csvTemplateContent(), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modele_import_questions_quiz.csv"',
        ]);
    }

    public function generateIA(Request $request, ModuleLecture $lecture)
    {
        $this->acces->assertEditable($lecture->module);
        $validated = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:15'],
        ]);

        try {
            $created = $this->genererQuestionsQuizIA->execute(
                $lecture,
                (int) $validated['count'],
                (int) auth()->id(),
            );
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return back()->with('error', "La génération par l'IA a pris trop de temps. Réessayez.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.quiz.questions.index', $lecture)
            ->with('success', $created.' question(s) générée(s) par l’IA, à relire avant activation.');
    }

    public function moduleIndex(Request $request, Module $module)
    {
        $this->acces->assertCatalogue($module);
        $module->load('sections.lectures');
        $lectureIds = $module->sections->flatMap->lectures->pluck('id');
        $lectureId = $request->integer('lecture');
        $lecture = $module->sections
            ->flatMap->lectures
            ->firstWhere('id', $lectureIds->contains($lectureId) ? $lectureId : $lectureIds->first());

        if (! $lecture) {
            return back()->with('error', 'Ajoutez une leçon avant de gérer sa banque de questions.');
        }

        return redirect()->route('admin.quiz.questions.index', $lecture);
    }

    public function move(Request $request, QuizQuestion $question)
    {
        $lecture = $question->lecture;
        $this->acces->assertEditable($lecture->module);
        $validated = $request->validate([
            'lecture_id' => ['required', 'integer', 'exists:module_lectures,id'],
        ]);

        $cible = ModuleLecture::query()
            ->whereKey($validated['lecture_id'])
            ->where('module_id', $lecture->module_id)
            ->firstOrFail();

        $question->update(['lecture_id' => $cible->id]);

        return redirect()
            ->route('admin.quiz.questions.index', $cible)
            ->with('success', 'Question déplacée vers « '.$cible->lecture_title.' ».');
    }
}
