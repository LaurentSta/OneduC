<?php

namespace App\Http\Controllers\Formateur;

use App\Domains\ModulesFormateur\Actions\CreerQuestionQuiz;
use App\Domains\ModulesFormateur\Actions\GenererQuestionsQuizIA;
use App\Domains\ModulesFormateur\Actions\ModifierQuestionQuiz;
use App\Domains\ModulesFormateur\Actions\SupprimerQuestionQuiz;
use App\Domains\ModulesFormateur\Support\AccesModule;
use App\Http\Controllers\Controller;
use App\Models\ModuleLecture;
use App\Models\QuizQuestion;
use App\Services\QuizQuestionBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class QuizQuestionController extends Controller
{
    public function __construct(
        private readonly AccesModule $access,
        private readonly QuizQuestionBuilder $builder,
        private readonly CreerQuestionQuiz $creerQuestionQuiz,
        private readonly ModifierQuestionQuiz $modifierQuestionQuiz,
        private readonly SupprimerQuestionQuiz $supprimerQuestionQuiz,
        private readonly GenererQuestionsQuizIA $genererQuestionsQuizIA,
    ) {}

    /**
     * Ancien point d'entrée par leçon : redirige vers la vue unifiée par formation.
     */
    public function index(ModuleLecture $lecture)
    {
        $this->access->assertOwner($lecture->module);

        return redirect()->route('formateur.modules.builder.quiz-questions.index', [
            'module' => $lecture->module_id,
            'lecture' => $lecture->id,
        ]);
    }

    public function create(ModuleLecture $lecture)
    {
        $this->access->assertOwner($lecture->module);

        return view('formateur.modules-builder.quiz-questions.create', [
            'lecture' => $lecture,
        ]);
    }

    public function store(Request $request, ModuleLecture $lecture)
    {
        $this->access->assertOwner($lecture->module);

        $data = $this->builder->validatePayload($request);

        $this->creerQuestionQuiz->execute($lecture, $data, $request);

        return redirect()
            ->route('formateur.modules.builder.quiz-questions.index', ['module' => $lecture->module_id, 'lecture' => $lecture->id])
            ->with('success', 'Question créée avec succès.');
    }

    public function generateIA(Request $request, ModuleLecture $lecture)
    {
        $this->access->assertOwner($lecture->module);

        $validated = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:15'],
        ]);

        try {
            $created = $this->genererQuestionsQuizIA->execute($lecture, (int) $validated['count'], (int) auth()->id());
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return back()->with('error', "La génération par l'IA a pris trop de temps. Réessayez.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('formateur.modules.builder.quiz-questions.index', ['module' => $lecture->module_id, 'lecture' => $lecture->id])
            ->with('success', "{$created} question(s) générée(s) par l'IA, à relire et activer avant utilisation.");
    }

    public function edit(ModuleLecture $lecture, QuizQuestion $question)
    {
        $this->access->assertOwner($lecture->module);
        abort_unless($question->lecture_id === $lecture->id, 404);

        $question->load(['options' => fn ($q) => $q->orderBy('position')]);

        return view('formateur.modules-builder.quiz-questions.edit', [
            'lecture' => $lecture,
            'question' => $question,
        ]);
    }

    public function update(Request $request, ModuleLecture $lecture, QuizQuestion $question)
    {
        $this->access->assertOwner($lecture->module);
        abort_unless($question->lecture_id === $lecture->id, 404);

        $data = $this->builder->validatePayload($request);

        $this->modifierQuestionQuiz->execute($question, $data, $request);

        return redirect()
            ->route('formateur.modules.builder.quiz-questions.index', ['module' => $lecture->module_id, 'lecture' => $lecture->id])
            ->with('success', 'Question mise à jour.');
    }

    public function destroy(ModuleLecture $lecture, QuizQuestion $question)
    {
        $this->access->assertOwner($lecture->module);
        abort_unless($question->lecture_id === $lecture->id, 404);

        $this->supprimerQuestionQuiz->execute($question);

        return redirect()
            ->route('formateur.modules.builder.quiz-questions.index', ['module' => $lecture->module_id, 'lecture' => $lecture->id])
            ->with('success', 'Question supprimée.');
    }

    public function importCsv(Request $request, ModuleLecture $lecture)
    {
        $this->access->assertOwner($lecture->module);

        $validated = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $result = $this->builder->importCsv($lecture, $validated['csv_file'], (int) auth()->id());

        $redirect = redirect()
            ->route('formateur.modules.builder.quiz-questions.index', ['module' => $lecture->module_id, 'lecture' => $lecture->id])
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

    public function downloadCsvTemplate(ModuleLecture $lecture)
    {
        $this->access->assertOwner($lecture->module);

        return response($this->builder->csvTemplateContent(), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modele_import_questions_quiz.csv"',
        ]);
    }
}
