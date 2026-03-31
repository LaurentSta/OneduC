<?php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Controller;
use App\Models\LiveQuizSession;
use App\Models\LiveQuizSessionParticipant;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptQuestion;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Support\LiveQuiz\InteractsWithLiveQuizAttempts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LiveQuizSessionController extends Controller
{
    use InteractsWithLiveQuizAttempts;

    public function index(): View
    {
        return view('stagiaire.live-quiz.index');
    }

    public function lookup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'min:4', 'max:12'],
        ]);

        $code = strtoupper(trim((string) $validated['code']));

        $session = LiveQuizSession::query()
            ->where('access_code', $code)
            ->first();

        if (! $session) {
            return back()
                ->withInput()
                ->withErrors([
                    'code' => 'Aucune session presentielle ne correspond a ce code.',
                ]);
        }

        return redirect()->route('stagiaire.live-quiz.join-code', ['code' => $code]);
    }

    public function notificationStatus(): JsonResponse
    {
        $session = $this->resolveActiveSessionForUser(auth()->user());

        return response()->json([
            'has_active_session' => ! is_null($session),
            'access_code' => $session?->access_code,
            'lecture_title' => $session?->lecture?->lecture_title,
            'join_url' => $session
                ? route('stagiaire.live-quiz.join-code', ['code' => $session->access_code])
                : null,
        ]);
    }

    public function joinByCode(string $code): View|RedirectResponse
    {
        $session = LiveQuizSession::query()
            ->with([
                'module',
                'section',
                'lecture',
                'sessionQuestions.question.options' => fn ($query) => $query->orderBy('position')->orderBy('id'),
            ])
            ->where('access_code', strtoupper(trim($code)))
            ->firstOrFail();

        $participant = $session->participants()
            ->where('user_id', auth()->id())
            ->first();

        if ($participant) {
            return redirect()->route('stagiaire.live-quiz.show', ['session' => $session->id]);
        }

        return view('stagiaire.live-quiz.join', [
            'session' => $session,
            'module' => $session->module,
            'section' => $session->section,
            'lecture' => $session->lecture,
            'selectedLecture' => $session->lecture,
        ]);
    }

    public function join(LiveQuizSession $session): RedirectResponse
    {
        $participant = $session->participants()
            ->where('user_id', auth()->id())
            ->first();

        if ($participant) {
            $participant->update(['last_seen_at' => now()]);

            return redirect()->route('stagiaire.live-quiz.show', ['session' => $session->id]);
        }

        abort_if($session->isClosed(), 403, 'Cette session est deja terminee.');

        DB::transaction(function () use ($session): void {
            $attempt = $this->createAttemptForSession((int) auth()->id(), $session->lecture, $session);

            LiveQuizSessionParticipant::query()->create([
                'live_quiz_session_id' => $session->id,
                'user_id' => auth()->id(),
                'attempt_id' => $attempt->id,
                'joined_at' => now(),
                'last_seen_at' => now(),
            ]);
        });

        return redirect()->route('stagiaire.live-quiz.show', ['session' => $session->id]);
    }

    public function show(LiveQuizSession $session): View|RedirectResponse
    {
        $session->load([
            'module',
            'section',
            'lecture',
            'sessionQuestions.question.options' => fn ($query) => $query->orderBy('position')->orderBy('id'),
        ]);

        $participant = $this->participantForCurrentUser($session);
        if (! $participant) {
            return redirect()->route('stagiaire.live-quiz.join-code', ['code' => $session->access_code]);
        }

        $participant->load([
            'attempt.attemptQuestions.question.options' => fn ($query) => $query->orderBy('position')->orderBy('id'),
        ]);

        $participant->update(['last_seen_at' => now()]);

        $attempt = $participant->attempt;
        $currentPosition = (int) $session->current_position;
        $currentAttemptQuestion = $currentPosition > 0
            ? $attempt?->attemptQuestions?->firstWhere('position', $currentPosition)
            : null;
        $currentQuestion = $currentAttemptQuestion?->question;
        $navigation = $this->resolveContinuationUrls($session);

        return view('stagiaire.live-quiz.show', [
            'session' => $session,
            'participant' => $participant,
            'attempt' => $attempt,
            'module' => $session->module,
            'section' => $session->section,
            'lecture' => $session->lecture,
            'selectedLecture' => $session->lecture,
            'currentAttemptQuestion' => $currentAttemptQuestion,
            'currentQuestion' => $currentQuestion,
            'correction' => $currentQuestion && $session->isAnswerRevealed()
                ? $this->buildCorrection($currentQuestion, $currentAttemptQuestion)
                : null,
            'summary' => $attempt ? $this->buildAttemptSummary($attempt) : null,
            'snapshot' => $this->buildSnapshot($session, $participant),
            'returnToLessonUrl' => $navigation['return_to_lesson_url'],
            'nextUrl' => $navigation['next_url'],
        ]);
    }

    public function answer(Request $request, LiveQuizSession $session): RedirectResponse
    {
        $participant = $this->participantOrFail($session);

        abort_if($session->isClosed(), 403);
        abort_unless($session->isQuestionOpen(), 403);
        abort_if((int) $session->current_position <= 0, 422);

        $attempt = $participant->attempt()->with([
            'attemptQuestions.question.options' => fn ($query) => $query->orderBy('position')->orderBy('id'),
        ])->firstOrFail();

        $attemptQuestion = $attempt->attemptQuestions->firstWhere('position', (int) $session->current_position);
        abort_unless($attemptQuestion, 404);
        abort_if(! is_null($attemptQuestion->answered_at), 403);

        $question = $attemptQuestion->question;
        abort_unless($question instanceof QuizQuestion, 404);

        if (in_array((string) $question->type, ['single', 'boolean'], true)) {
            $request->validate([
                'answer' => ['required', 'integer'],
                'time_spent' => ['nullable', 'integer', 'min:0', 'max:600'],
            ], [
                'answer.required' => 'Veuillez répondre en sélectionnant une seule réponse avant de valider.',
            ]);
        } elseif ((string) $question->type === 'multiple') {
            $request->validate([
                'answers' => ['required', 'array', 'min:1'],
                'answers.*' => ['integer'],
                'time_spent' => ['nullable', 'integer', 'min:0', 'max:600'],
            ], [
                'answers.required' => 'Veuillez sélectionner au moins une réponse avant de valider.',
                'answers.min' => 'Veuillez sélectionner au moins une réponse avant de valider.',
            ]);
        } elseif ((string) $question->type === 'cloze') {
            $request->validate([
                'answers' => ['required', 'array', 'min:1'],
                'answers.*' => ['nullable', 'string'],
                'time_spent' => ['nullable', 'integer', 'min:0', 'max:600'],
            ], [
                'answers.required' => 'Veuillez compléter les champs avant de valider.',
                'answers.min' => 'Veuillez compléter les champs avant de valider.',
            ]);
        } else {
            abort(422, 'Type de question non pris en charge.');
        }

        $graded = $this->gradeLiveAnswer($question, $request->all());
        $duration = (int) $request->integer('time_spent', 0);

        $this->updateAttemptQuestionWithLiveAnswer($attemptQuestion, $question, $graded, $duration);
        $participant->update(['last_seen_at' => now()]);

        return redirect()->route('stagiaire.live-quiz.show', ['session' => $session->id]);
    }

    public function snapshot(LiveQuizSession $session): JsonResponse
    {
        $participant = $this->participantOrFail($session);

        $participant->update(['last_seen_at' => now()]);

        return response()->json($this->buildSnapshot($session->fresh(), $participant->fresh('attempt')));
    }

    private function participantForCurrentUser(LiveQuizSession $session): ?LiveQuizSessionParticipant
    {
        return $session->participants()
            ->where('user_id', auth()->id())
            ->first();
    }

    private function participantOrFail(LiveQuizSession $session): LiveQuizSessionParticipant
    {
        $participant = $this->participantForCurrentUser($session);
        abort_unless($participant, 403);

        return $participant;
    }

    private function buildCorrection(QuizQuestion $question, ?QuizAttemptQuestion $attemptQuestion): array
    {
        if ((string) $question->type === 'cloze') {
            $given = is_array($attemptQuestion?->given_answer ?? null) ? $attemptQuestion->given_answer : [];
            $givenBlanks = is_array($given['blanks'] ?? null) ? $given['blanks'] : [];
            $configuredBlanks = is_array(data_get($question->payload, 'blanks')) ? data_get($question->payload, 'blanks') : [];
            $merged = [];

            foreach ($configuredBlanks as $blankKey => $blankConfig) {
                $merged[$blankKey] = [
                    'answer' => data_get($givenBlanks, $blankKey . '.answer', ''),
                    'accepted_answers' => is_array($blankConfig['accepted_answers'] ?? null) ? $blankConfig['accepted_answers'] : [],
                    'is_correct' => (bool) data_get($givenBlanks, $blankKey . '.is_correct', false),
                ];
            }

            return [
                'type' => 'cloze',
                'blanks' => $merged,
            ];
        }

        return [
            'type' => (string) $question->type,
            'correct_options' => $question->options
                ->where('is_correct', true)
                ->values(),
        ];
    }

    private function buildAttemptSummary(QuizAttempt $attempt): array
    {
        $rows = $attempt->attemptQuestions ?? collect();
        $answered = $rows->filter(fn ($row) => ! is_null($row->answered_at));
        $correct = $answered->where('is_correct', true);

        return [
            'answered' => $answered->count(),
            'correct' => $correct->count(),
            'total' => $rows->count(),
            'score' => (int) ($attempt->percent ?? 0),
            'is_finished' => ! is_null($attempt->finished_at),
        ];
    }

    private function resolveActiveSessionForUser(?User $user): ?LiveQuizSession
    {
        if (! $user || ($user->role ?? null) !== 'stagiaire') {
            return null;
        }

        $lectureIds = $user->groupesStagiaire()
            ->active()
            ->with(['modules.sections.lectures:id,section_id,module_id'])
            ->get()
            ->flatMap->modules
            ->unique('id')
            ->flatMap(fn ($module) => $module->sections ?? collect())
            ->flatMap(fn ($section) => $section->lectures ?? collect())
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        if ($lectureIds->isEmpty()) {
            return null;
        }

        return LiveQuizSession::query()
            ->with('lecture:id,lecture_title')
            ->whereIn('lecture_id', $lectureIds->all())
            ->whereNull('ended_at')
            ->whereIn('status', [
                LiveQuizSession::STATUS_QUESTION_OPEN,
                LiveQuizSession::STATUS_ANSWER_REVEALED,
            ])
            ->latest('id')
            ->first();
    }

    private function resolveContinuationUrls(LiveQuizSession $session): array
    {
        $returnToLessonUrl = route('stagiaire.module.lecture', [
            'module' => $session->module_id,
            'section' => $session->section_id,
            'lecture' => $session->lecture_id,
        ]);

        $module = $session->module()->with([
            'sections' => function ($query) {
                $query->orderBy('id')
                    ->with(['lectures' => function ($lectureQuery) {
                        $lectureQuery->orderBy('position')->orderBy('id');
                    }]);
            },
        ])->first();

        if (! $module) {
            return [
                'return_to_lesson_url' => $returnToLessonUrl,
                'next_url' => route('stagiaire.module.fin', ['module' => $session->module_id]),
            ];
        }

        $section = $module->sections->firstWhere('id', (int) $session->section_id);
        $sectionLectures = $section?->lectures?->values() ?? collect();
        $currentIndex = $sectionLectures->search(fn ($lecture) => (int) $lecture->id === (int) $session->lecture_id);

        if ($currentIndex !== false && $currentIndex < ($sectionLectures->count() - 1)) {
            $nextLecture = $sectionLectures->get($currentIndex + 1);

            return [
                'return_to_lesson_url' => $returnToLessonUrl,
                'next_url' => route('stagiaire.module.lecture', [
                    'module' => $session->module_id,
                    'section' => $session->section_id,
                    'lecture' => $nextLecture->id,
                ]),
            ];
        }

        $nextSection = $module->sections
            ->filter(fn ($candidate) => (int) $candidate->id > (int) $session->section_id)
            ->sortBy('id')
            ->first(fn ($candidate) => $candidate->lectures->isNotEmpty());

        if ($nextSection) {
            $nextLecture = $nextSection->lectures->sortBy([
                ['position', 'asc'],
                ['id', 'asc'],
            ])->first();

            return [
                'return_to_lesson_url' => $returnToLessonUrl,
                'next_url' => $nextLecture
                    ? route('stagiaire.module.lecture', [
                        'module' => $session->module_id,
                        'section' => $nextSection->id,
                        'lecture' => $nextLecture->id,
                    ])
                    : route('stagiaire.module.section', [
                    'module' => $session->module_id,
                    'section' => $nextSection->id,
                    ]),
            ];
        }

        return [
            'return_to_lesson_url' => $returnToLessonUrl,
            'next_url' => route('stagiaire.module.fin', ['module' => $session->module_id]),
        ];
    }

    private function buildSnapshot(LiveQuizSession $session, LiveQuizSessionParticipant $participant): array
    {
        $attempt = $participant->attempt()->with('attemptQuestions')->first();
        $currentPosition = (int) $session->current_position;
        $currentAttemptQuestion = $attempt?->attemptQuestions?->firstWhere('position', $currentPosition);

        return [
            'state_key' => implode('|', [
                (string) $session->status,
                $currentPosition,
                optional($session->answer_revealed_at)->timestamp ?: 0,
                optional($session->ended_at)->timestamp ?: 0,
                optional($currentAttemptQuestion?->answered_at)->timestamp ?: 0,
            ]),
            'status' => (string) $session->status,
            'current_position' => $currentPosition,
            'total_questions' => (int) $session->total_questions,
            'answer_revealed' => $session->isAnswerRevealed(),
            'has_answered_current' => ! is_null($currentAttemptQuestion?->answered_at),
        ];
    }
}
