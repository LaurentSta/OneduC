<?php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupQuizSession;
use App\Models\GroupQuizSessionAnswer;
use App\Models\GroupQuizSessionParticipant;
use App\Models\User;
use App\Services\QuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupQuizSessionController extends Controller
{
    public function __construct(private readonly QuizService $quizService) {}

    public function notificationStatus(): JsonResponse
    {
        $session = $this->resolveActiveSessionForUser(auth()->user());
        $session?->loadMissing('group');

        return response()->json([
            'has_active_session' => ! is_null($session),
            'access_code' => $session?->access_code,
            'group_name' => $session ? ($session->group?->name ?? 'Tous les groupes') : null,
            'join_url' => $session
                ? route('stagiaire.group-quiz.join-code', ['code' => $session->access_code])
                : null,
        ]);
    }

    public function joinByCode(string $code): RedirectResponse
    {
        $session = GroupQuizSession::query()
            ->where('access_code', strtoupper(trim($code)))
            ->firstOrFail();

        $this->assertMember($session);

        return redirect()->route('stagiaire.group-quiz.show', ['session' => $session->id]);
    }

    public function show(GroupQuizSession $session): View
    {
        $participant = $this->participantFor($session);
        $participant->update(['last_seen_at' => now()]);

        $session->load([
            'sessionQuestions.question.options' => fn ($q) => $q->orderBy('position')->orderBy('id'),
        ]);

        $currentSessionQuestion = $session->currentSessionQuestion();
        $currentQuestion = $currentSessionQuestion?->question;
        $myAnswer = $currentSessionQuestion
            ? GroupQuizSessionAnswer::where('group_quiz_session_question_id', $currentSessionQuestion->id)
                ->where('user_id', auth()->id())
                ->first()
            : null;

        return view('stagiaire.group-quiz.show', [
            'session' => $session,
            'currentQuestion' => $currentQuestion,
            'myAnswer' => $myAnswer,
            'correction' => $currentQuestion && $session->isAnswerRevealed()
                ? $this->buildCorrection($currentQuestion)
                : null,
            'snapshot' => $this->buildSnapshot($session, $participant),
        ]);
    }

    public function answer(Request $request, GroupQuizSession $session): RedirectResponse
    {
        $participant = $this->participantFor($session);

        abort_if($session->isClosed(), 403);
        abort_unless($session->isQuestionOpen(), 403);

        $sessionQuestion = $session->currentSessionQuestion();
        abort_unless($sessionQuestion, 404);

        $already = GroupQuizSessionAnswer::where('group_quiz_session_question_id', $sessionQuestion->id)
            ->where('user_id', auth()->id())
            ->exists();
        abort_if($already, 403);

        $question = $sessionQuestion->question()->with('options')->first();
        abort_unless($question, 404);

        $type = (string) $question->type;

        if (in_array($type, ['single', 'boolean'], true)) {
            $request->validate([
                'answer' => ['required', 'integer'],
            ], ['answer.required' => 'Veuillez sélectionner une réponse avant de valider.']);
        } elseif ($type === 'multiple') {
            $request->validate([
                'answers' => ['required', 'array', 'min:1'],
                'answers.*' => ['integer'],
            ], ['answers.required' => 'Veuillez sélectionner au moins une réponse avant de valider.']);
        } elseif ($type === 'cloze') {
            $request->validate([
                'answers' => ['required', 'array', 'min:1'],
                'answers.*' => ['nullable', 'string'],
            ], ['answers.required' => 'Veuillez compléter les champs avant de valider.']);
        } else {
            abort(422, 'Type de question non pris en charge.');
        }

        $graded = $this->quizService->gradeAnswer($question, $request->all());

        GroupQuizSessionAnswer::create([
            'group_quiz_session_question_id' => $sessionQuestion->id,
            'user_id' => auth()->id(),
            'answer_option_ids' => $graded['answer_option_ids'],
            'given_answer' => $graded['given_answer'],
            'is_correct' => $graded['is_correct'],
            'answered_at' => now(),
        ]);

        $participant->update(['last_seen_at' => now()]);

        return redirect()->route('stagiaire.group-quiz.show', ['session' => $session->id]);
    }

    public function snapshot(GroupQuizSession $session): JsonResponse
    {
        $participant = $this->participantFor($session);

        if (! $participant->last_seen_at || $participant->last_seen_at->lt(now()->subSeconds(10))) {
            $participant->update(['last_seen_at' => now()]);
        }

        return response()->json($this->buildSnapshot($session->fresh(), $participant));
    }

    private function participantFor(GroupQuizSession $session): GroupQuizSessionParticipant
    {
        $this->assertMember($session);

        return GroupQuizSessionParticipant::query()->firstOrCreate(
            ['group_quiz_session_id' => $session->id, 'user_id' => auth()->id()],
            ['joined_at' => now(), 'last_seen_at' => now()]
        );
    }

    private function assertMember(GroupQuizSession $session): void
    {
        $userId = (int) auth()->id();

        $isMember = Group::query()
            ->where('instructor_id', $session->formateur_id)
            ->when($session->group_id, fn ($q) => $q->where('id', $session->group_id))
            ->whereHas('students', fn ($q) => $q->where('users.id', $userId))
            ->exists();

        abort_unless($isMember, 403);
    }

    private function buildCorrection($question): array
    {
        if ((string) $question->type === 'cloze') {
            return [
                'type' => 'cloze',
                'blanks' => is_array(data_get($question->payload, 'blanks')) ? data_get($question->payload, 'blanks') : [],
            ];
        }

        return [
            'type' => (string) $question->type,
            'correct_options' => $question->options->where('is_correct', true)->values(),
        ];
    }

    private function resolveActiveSessionForUser(?User $user): ?GroupQuizSession
    {
        if (! $user || ($user->role ?? null) !== 'stagiaire') {
            return null;
        }

        $groupIds = $user->groupesStagiaire()->active()->pluck('groups.id');
        if ($groupIds->isEmpty()) {
            return null;
        }

        $instructorIds = Group::query()->whereIn('id', $groupIds)->pluck('instructor_id')->unique()->values();

        return GroupQuizSession::query()
            ->whereNull('ended_at')
            ->where(function ($query) use ($groupIds, $instructorIds) {
                $query->whereIn('group_id', $groupIds)
                    ->orWhere(function ($sub) use ($instructorIds) {
                        $sub->whereNull('group_id')->whereIn('formateur_id', $instructorIds);
                    });
            })
            ->latest('id')
            ->first();
    }

    private function buildSnapshot(GroupQuizSession $session, GroupQuizSessionParticipant $participant): array
    {
        $currentSessionQuestion = $session->currentSessionQuestion();
        $myAnswer = $currentSessionQuestion
            ? GroupQuizSessionAnswer::where('group_quiz_session_question_id', $currentSessionQuestion->id)
                ->where('user_id', $participant->user_id)
                ->first()
            : null;

        return [
            'state_key' => implode('|', [
                (string) $session->status,
                (int) $session->current_position,
                optional($session->answer_revealed_at)->timestamp ?: 0,
                optional($session->ended_at)->timestamp ?: 0,
                optional($myAnswer?->answered_at)->timestamp ?: 0,
            ]),
            'status' => (string) $session->status,
            'current_position' => (int) $session->current_position,
            'total_questions' => (int) $session->total_questions,
            'answer_revealed' => $session->isAnswerRevealed(),
            'has_answered_current' => ! is_null($myAnswer),
        ];
    }
}
