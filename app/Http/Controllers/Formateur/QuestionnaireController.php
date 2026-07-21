<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionnaire;
use App\Services\QuizQuestionnaireAIGenerator;
use App\Services\QuizQuestionnaireBuilder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class QuestionnaireController extends Controller
{
    public function __construct(private readonly QuizQuestionnaireBuilder $builder) {}

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $questionnaire = QuizQuestionnaire::query()->create([
            'formateur_id' => auth()->id(),
            'title' => $data['title'],
        ]);

        return $this->withWizardContext(
            redirect()->route('formateur.outils.quiz.index')->with('success', 'Questionnaire créé.'),
            $questionnaire
        );
    }

    public function destroy(QuizQuestionnaire $questionnaire): RedirectResponse
    {
        $this->assertOwnership($questionnaire);

        $questionnaire->delete();

        return redirect()
            ->route('formateur.outils.quiz.index')
            ->with('success', 'Questionnaire supprimé.');
    }

    public function storeQuestion(QuizQuestionnaire $questionnaire, Request $request): RedirectResponse
    {
        $this->assertOwnership($questionnaire);

        $data = $this->builder->validatePayload($request);
        $this->builder->createQuestion($questionnaire, $data, (int) auth()->id());

        $redirect = redirect()
            ->route('formateur.outils.quiz.index')
            ->with('success', 'Question ajoutée au questionnaire.');

        return $request->boolean('wizard') ? $this->withWizardContext($redirect, $questionnaire) : $redirect;
    }

    public function destroyQuestion(QuizQuestionnaire $questionnaire, Request $request, QuizQuestion $question): RedirectResponse
    {
        $this->assertOwnership($questionnaire);
        abort_unless((int) $question->questionnaire_id === (int) $questionnaire->id, 404);

        $this->builder->deleteQuestion($question);

        $redirect = redirect()
            ->route('formateur.outils.quiz.index')
            ->with('success', 'Question supprimée.');

        return $request->boolean('wizard') ? $this->withWizardContext($redirect, $questionnaire) : $redirect;
    }

    public function generateIA(QuizQuestionnaire $questionnaire, Request $request, QuizQuestionnaireAIGenerator $generator): RedirectResponse
    {
        $this->assertOwnership($questionnaire);
        $isWizard = $request->boolean('wizard');

        $validated = $request->validate([
            'topic' => ['required', 'string', 'max:500'],
            'count' => ['required', 'integer', 'min:1', 'max:15'],
        ]);

        try {
            $created = $generator->execute($questionnaire, $validated['topic'], (int) $validated['count'], (int) auth()->id());
        } catch (ConnectionException $e) {
            $redirect = back()->with('error', "La génération par l'IA a pris trop de temps. Réessayez.");

            return $isWizard ? $this->withWizardContext($redirect, $questionnaire) : $redirect;
        } catch (RuntimeException $e) {
            $redirect = back()->with('error', $e->getMessage());

            return $isWizard ? $this->withWizardContext($redirect, $questionnaire) : $redirect;
        }

        $redirect = redirect()
            ->route('formateur.outils.quiz.index')
            ->with('success', "{$created} question(s) générée(s) par l'IA, à relire et activer avant utilisation.");

        return $isWizard ? $this->withWizardContext($redirect, $questionnaire) : $redirect;
    }

    public function toggleQuestion(QuizQuestionnaire $questionnaire, Request $request, QuizQuestion $question): RedirectResponse
    {
        $this->assertOwnership($questionnaire);
        abort_unless((int) $question->questionnaire_id === (int) $questionnaire->id, 404);

        $question->update(['is_active' => ! $question->is_active]);

        $redirect = redirect()
            ->route('formateur.outils.quiz.index')
            ->with('success', $question->is_active ? 'Question activée.' : 'Question désactivée.');

        return $request->boolean('wizard') ? $this->withWizardContext($redirect, $questionnaire) : $redirect;
    }

    private function withWizardContext(RedirectResponse $redirect, QuizQuestionnaire $questionnaire): RedirectResponse
    {
        $activeCount = QuizQuestion::where('questionnaire_id', $questionnaire->id)
            ->where('is_active', true)
            ->count();

        return $redirect
            ->with('wizard_step', 3)
            ->with('wizard_questionnaire_id', (string) $questionnaire->id)
            ->with('wizard_questionnaire_title', $questionnaire->title)
            ->with('wizard_questionnaire_active', $activeCount);
    }

    private function assertOwnership(QuizQuestionnaire $questionnaire): void
    {
        abort_unless((int) $questionnaire->formateur_id === (int) auth()->id(), 403);
    }
}
