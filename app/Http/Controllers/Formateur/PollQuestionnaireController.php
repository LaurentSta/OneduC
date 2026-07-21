<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\PollQuestionnaire;
use App\Services\PollQuestionnaireBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PollQuestionnaireController extends Controller
{
    public function __construct(private readonly PollQuestionnaireBuilder $builder) {}

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $questionnaire = PollQuestionnaire::query()->create([
            'formateur_id' => auth()->id(),
            'title' => $data['title'],
            'questions' => [],
        ]);

        return $this->withWizardContext(
            redirect()->route('formateur.sondages.index')->with('success', 'Sondage créé.'),
            $questionnaire
        );
    }

    public function destroy(PollQuestionnaire $questionnaire): RedirectResponse
    {
        $this->assertOwnership($questionnaire);

        $questionnaire->delete();

        return redirect()
            ->route('formateur.sondages.index')
            ->with('success', 'Sondage supprimé.');
    }

    public function storeQuestion(PollQuestionnaire $questionnaire, Request $request): RedirectResponse
    {
        $this->assertOwnership($questionnaire);

        $data = $this->builder->validatePayload($request);
        $this->builder->addQuestion($questionnaire, $data);

        $redirect = redirect()
            ->route('formateur.sondages.index')
            ->with('success', 'Question ajoutée au sondage.');

        return $request->boolean('wizard') ? $this->withWizardContext($redirect, $questionnaire->fresh()) : $redirect;
    }

    public function destroyQuestion(PollQuestionnaire $questionnaire, Request $request, int $index): RedirectResponse
    {
        $this->assertOwnership($questionnaire);

        $this->builder->removeQuestion($questionnaire, $index);

        $redirect = redirect()
            ->route('formateur.sondages.index')
            ->with('success', 'Question supprimée.');

        return $request->boolean('wizard') ? $this->withWizardContext($redirect, $questionnaire->fresh()) : $redirect;
    }

    private function withWizardContext(RedirectResponse $redirect, PollQuestionnaire $questionnaire): RedirectResponse
    {
        return $redirect
            ->with('sondage_wizard_step', 3)
            ->with('sondage_wizard_questionnaire_id', (string) $questionnaire->id)
            ->with('sondage_wizard_questionnaire_title', $questionnaire->title)
            ->with('sondage_wizard_questionnaire_count', count($questionnaire->questions ?? []));
    }

    private function assertOwnership(PollQuestionnaire $questionnaire): void
    {
        abort_unless((int) $questionnaire->formateur_id === (int) auth()->id(), 403);
    }
}
