<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupQuizSession;
use App\Models\GroupQuizSessionAnswer;
use App\Models\QuizQuestionnaire;
use App\Services\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GroupQuizSessionController extends Controller
{
    public function launch(Request $request): RedirectResponse
    {
        $formateurId = (int) auth()->id();

        $data = $request->validate([
            'group_id' => ['nullable', 'exists:groups,id'],
            'questionnaire_id' => ['required', 'integer', 'exists:quiz_questionnaires,id'],
        ]);

        $group = null;
        if (! empty($data['group_id'])) {
            $group = Group::query()
                ->where('id', (int) $data['group_id'])
                ->where('instructor_id', $formateurId)
                ->firstOrFail();
        }

        $questionnaire = QuizQuestionnaire::query()
            ->where('formateur_id', $formateurId)
            ->where('id', (int) $data['questionnaire_id'])
            ->with(['questions' => fn ($q) => $q->where('is_active', true)])
            ->firstOrFail();

        $questions = $questionnaire->questions;

        if ($questions->isEmpty()) {
            return back()->withErrors([
                'questionnaire_id' => 'Ce questionnaire ne contient aucune question active.',
            ]);
        }

        $session = DB::transaction(function () use ($formateurId, $group, $questions): GroupQuizSession {
            $session = GroupQuizSession::query()->create([
                'formateur_id' => $formateurId,
                'group_id' => $group?->id,
                'access_code' => CodeGeneratorService::generateUniqueCode(GroupQuizSession::class),
                'status' => GroupQuizSession::STATUS_WAITING,
                'current_position' => 0,
                'total_questions' => $questions->count(),
            ]);

            foreach ($questions as $index => $question) {
                $session->sessionQuestions()->create([
                    'question_id' => $question->id,
                    'position' => $index + 1,
                ]);
            }

            return $session;
        });

        return redirect()
            ->route('formateur.group-quiz.show', $session)
            ->with('success', 'Le quiz en direct est prêt. Les stagiaires peuvent rejoindre avec le code.');
    }

    public function show(GroupQuizSession $session): View
    {
        $this->assertOwnership($session);

        $session->load([
            'group',
            'sessionQuestions.question.options' => fn ($q) => $q->orderBy('position')->orderBy('id'),
        ]);

        return view('formateur.group-quiz.show', [
            'session' => $session,
            'joinUrl' => route('stagiaire.group-quiz.join-code', ['code' => $session->access_code]),
            'snapshot' => $this->buildSnapshot($session),
        ]);
    }

    public function start(GroupQuizSession $session): RedirectResponse
    {
        $this->assertOwnership($session);
        abort_if($session->isClosed(), 403);

        $session->update([
            'status' => GroupQuizSession::STATUS_QUESTION_OPEN,
            'current_position' => max(1, (int) $session->current_position),
            'started_at' => $session->started_at ?: now(),
            'answer_revealed_at' => null,
        ]);

        return back()->with('success', 'La première question est visible pour les stagiaires.');
    }

    public function reveal(GroupQuizSession $session): RedirectResponse
    {
        $this->assertOwnership($session);
        abort_if($session->isClosed(), 403);
        abort_if((int) $session->current_position <= 0, 422);

        $session->update([
            'status' => GroupQuizSession::STATUS_ANSWER_REVEALED,
            'answer_revealed_at' => now(),
        ]);

        return back()->with('success', 'La bonne réponse est affichée.');
    }

    public function next(GroupQuizSession $session): RedirectResponse
    {
        $this->assertOwnership($session);
        abort_if($session->isClosed(), 403);

        $nextPosition = ((int) $session->current_position) + 1;

        if ($nextPosition > (int) $session->total_questions) {
            $this->closeSession($session);

            return back()->with('success', 'Le quiz en direct est terminé.');
        }

        $session->update([
            'status' => GroupQuizSession::STATUS_QUESTION_OPEN,
            'current_position' => $nextPosition,
            'answer_revealed_at' => null,
        ]);

        return back()->with('success', 'Question suivante envoyée.');
    }

    public function close(GroupQuizSession $session): RedirectResponse
    {
        $this->assertOwnership($session);
        $this->closeSession($session);

        return back()->with('success', 'Le quiz en direct est terminé.');
    }

    public function snapshot(GroupQuizSession $session): JsonResponse
    {
        $this->assertOwnership($session);

        return response()->json($this->buildSnapshot($session));
    }

    public function destroy(GroupQuizSession $session): RedirectResponse
    {
        $this->assertOwnership($session);

        $session->delete();

        return redirect()
            ->route('formateur.outils.quiz.index')
            ->with('success', 'Quiz en direct supprimé.');
    }

    private function closeSession(GroupQuizSession $session): void
    {
        $session->update([
            'status' => GroupQuizSession::STATUS_CLOSED,
            'answer_revealed_at' => $session->answer_revealed_at ?: now(),
            'ended_at' => $session->ended_at ?: now(),
        ]);
    }

    private function buildSnapshot(GroupQuizSession $session): array
    {
        $currentSessionQuestion = $session->currentSessionQuestion();
        $currentQuestion = $currentSessionQuestion?->question;

        $answers = $currentSessionQuestion
            ? GroupQuizSessionAnswer::where('group_quiz_session_question_id', $currentSessionQuestion->id)->get()
            : collect();

        $distribution = [];
        if ($currentQuestion && (string) $currentQuestion->type !== 'cloze') {
            foreach ($currentQuestion->options as $option) {
                $distribution[$option->id] = [
                    'id' => (int) $option->id,
                    'text' => (string) $option->option_text,
                    'count' => 0,
                    'is_correct' => (bool) $option->is_correct,
                ];
            }

            foreach ($answers as $answer) {
                foreach ((array) ($answer->answer_option_ids ?? []) as $optionId) {
                    if (isset($distribution[$optionId])) {
                        $distribution[$optionId]['count']++;
                    }
                }
            }
        }

        return [
            'state_key' => implode('|', [
                (string) $session->status,
                (int) $session->current_position,
                optional($session->answer_revealed_at)->timestamp ?: 0,
                optional($session->ended_at)->timestamp ?: 0,
            ]),
            'status' => (string) $session->status,
            'status_label' => $this->statusLabel($session),
            'current_position' => (int) $session->current_position,
            'total_questions' => (int) $session->total_questions,
            'participant_count' => $session->participants()->count(),
            'answered_count' => $answers->count(),
            'correct_count' => $answers->where('is_correct', true)->count(),
            'distribution' => array_values($distribution),
            'is_cloze' => (string) ($currentQuestion->type ?? '') === 'cloze',
        ];
    }

    private function statusLabel(GroupQuizSession $session): string
    {
        return match ((string) $session->status) {
            GroupQuizSession::STATUS_WAITING => 'En attente',
            GroupQuizSession::STATUS_QUESTION_OPEN => 'Question ouverte',
            GroupQuizSession::STATUS_ANSWER_REVEALED => 'Correction affichée',
            GroupQuizSession::STATUS_CLOSED => 'Terminée',
            default => 'Session',
        };
    }

    private function assertOwnership(GroupQuizSession $session): void
    {
        abort_unless((int) $session->formateur_id === (int) auth()->id(), 403);
    }
}
