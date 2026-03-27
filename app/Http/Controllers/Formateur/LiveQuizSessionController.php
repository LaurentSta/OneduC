<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\LiveQuizSession;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\QuizQuestion;
use App\Support\LiveQuiz\InteractsWithLiveQuizAttempts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LiveQuizSessionController extends Controller
{
    use InteractsWithLiveQuizAttempts;

    public function store(Request $request, Module $module, ModuleSection $section, ModuleLecture $lecture): RedirectResponse
    {
        $this->assertLectureContext($module, $section, $lecture);
        $group = null;

        if ($request->filled('group_id')) {
            $group = Group::query()
                ->where('id', (int) $request->input('group_id'))
                ->where('instructor_id', (int) auth()->id())
                ->firstOrFail();
        }

        return $this->createSessionAndRedirect($module, $section, $lecture, $group);
    }

    public function launch(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'module_id' => ['required', 'exists:modules,id'],
            'lecture_id' => ['required', 'exists:module_lectures,id'],
        ]);

        $group = Group::query()
            ->where('id', (int) $data['group_id'])
            ->where('instructor_id', (int) auth()->id())
            ->firstOrFail();

        $module = Module::query()
            ->where('id', (int) $data['module_id'])
            ->where('formateur_id', (int) auth()->id())
            ->firstOrFail();

        abort_unless($group->modules()->where('modules.id', $module->id)->exists(), 403);

        $lecture = ModuleLecture::query()
            ->where('id', (int) $data['lecture_id'])
            ->where('module_id', (int) $module->id)
            ->firstOrFail();

        $section = ModuleSection::query()
            ->where('id', (int) $lecture->section_id)
            ->where('module_id', (int) $module->id)
            ->firstOrFail();

        return $this->createSessionAndRedirect($module, $section, $lecture, $group);
    }

    public function show(Module $module, ModuleSection $section, ModuleLecture $lecture, LiveQuizSession $session): View
    {
        $this->assertSessionContext($session, $module, $section, $lecture);

        $session->load([
            'group',
            'sessionQuestions.question.options' => fn ($query) => $query->orderBy('position')->orderBy('id'),
            'participants.user',
            'participants.attempt.attemptQuestions',
        ]);

        $currentSessionQuestion = $session->currentSessionQuestion();
        $currentQuestion = $currentSessionQuestion?->question;

        return view('formateur.formations.live-quiz.show', [
            'module' => $module,
            'section' => $section,
            'lecture' => $lecture,
            'selectedLecture' => $lecture,
            'session' => $session,
            'currentSessionQuestion' => $currentSessionQuestion,
            'currentQuestion' => $currentQuestion,
            'joinUrl' => route('stagiaire.live-quiz.join-code', ['code' => $session->access_code]),
            'snapshot' => $this->buildSnapshot($session),
        ]);
    }

    public function start(Module $module, ModuleSection $section, ModuleLecture $lecture, LiveQuizSession $session): RedirectResponse
    {
        $this->assertSessionContext($session, $module, $section, $lecture);

        abort_if($session->isClosed(), 403);

        $session->update([
            'status' => LiveQuizSession::STATUS_QUESTION_OPEN,
            'current_position' => max(1, (int) $session->current_position),
            'started_at' => $session->started_at ?: now(),
            'answer_revealed_at' => null,
        ]);

        return back()->with('success', 'La session est demarree. La premiere question est visible pour les stagiaires.');
    }

    public function reveal(Module $module, ModuleSection $section, ModuleLecture $lecture, LiveQuizSession $session): RedirectResponse
    {
        $this->assertSessionContext($session, $module, $section, $lecture);

        abort_if($session->isClosed(), 403);
        abort_if((int) $session->current_position <= 0, 422);

        $session->update([
            'status' => LiveQuizSession::STATUS_ANSWER_REVEALED,
            'answer_revealed_at' => now(),
        ]);

        return back()->with('success', 'La bonne reponse est maintenant affichee pour la salle.');
    }

    public function next(Module $module, ModuleSection $section, ModuleLecture $lecture, LiveQuizSession $session): RedirectResponse
    {
        $this->assertSessionContext($session, $module, $section, $lecture);

        abort_if($session->isClosed(), 403);

        $nextPosition = ((int) $session->current_position) + 1;

        if ($nextPosition > (int) $session->total_questions) {
            $this->closeSession($session);

            return back()->with('success', 'La session presentielle est terminee et les tentatives ont ete enregistrees.');
        }

        $session->update([
            'status' => LiveQuizSession::STATUS_QUESTION_OPEN,
            'current_position' => $nextPosition,
            'answer_revealed_at' => null,
            'started_at' => $session->started_at ?: now(),
        ]);

        return back()->with('success', 'Question suivante envoyee a la salle.');
    }

    public function close(Module $module, ModuleSection $section, ModuleLecture $lecture, LiveQuizSession $session): RedirectResponse
    {
        $this->assertSessionContext($session, $module, $section, $lecture);

        $this->closeSession($session);

        return back()->with('success', 'La session a ete cloturee.');
    }

    public function snapshot(Module $module, ModuleSection $section, ModuleLecture $lecture, LiveQuizSession $session): JsonResponse
    {
        $this->assertSessionContext($session, $module, $section, $lecture);

        $session->load([
            'sessionQuestions.question.options' => fn ($query) => $query->orderBy('position')->orderBy('id'),
            'participants.attempt.attemptQuestions',
        ]);

        return response()->json($this->buildSnapshot($session));
    }

    private function buildSnapshot(LiveQuizSession $session): array
    {
        $currentPosition = (int) $session->current_position;
        $currentSessionQuestion = $session->currentSessionQuestion();
        $currentQuestion = $currentSessionQuestion?->question;

        $currentRows = collect();
        foreach ($session->participants as $participant) {
            $row = $participant->attempt?->attemptQuestions?->firstWhere('position', $currentPosition);
            if ($row) {
                $currentRows->push($row);
            }
        }

        $answeredRows = $currentRows->filter(fn ($row) => !is_null($row->answered_at));
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

            foreach ($answeredRows as $row) {
                foreach ($this->extractAnswerOptionIds($row->answer_option_ids ?? null) as $optionId) {
                    if (isset($distribution[$optionId])) {
                        $distribution[$optionId]['count']++;
                    }
                }
            }
        }

        return [
            'state_key' => implode('|', [
                (string) $session->status,
                $currentPosition,
                optional($session->answer_revealed_at)->timestamp ?: 0,
                optional($session->ended_at)->timestamp ?: 0,
            ]),
            'status' => (string) $session->status,
            'status_label' => $this->statusLabel($session),
            'current_position' => $currentPosition,
            'total_questions' => (int) $session->total_questions,
            'participant_count' => $session->participants->count(),
            'answered_count' => $answeredRows->count(),
            'pending_count' => max(0, $session->participants->count() - $answeredRows->count()),
            'correct_count' => $answeredRows->where('is_correct', true)->count(),
            'distribution' => array_values($distribution),
            'is_cloze' => (string) ($currentQuestion->type ?? '') === 'cloze',
        ];
    }

    private function closeSession(LiveQuizSession $session): void
    {
        $session->loadMissing('participants.attempt');

        DB::transaction(function () use ($session): void {
            foreach ($session->participants as $participant) {
                $attempt = $participant->attempt;
                if (! $attempt || ! is_null($attempt->finished_at)) {
                    continue;
                }

                $this->finalizeAttempt($attempt);
            }

            $session->update([
                'status' => LiveQuizSession::STATUS_CLOSED,
                'answer_revealed_at' => $session->answer_revealed_at ?: now(),
                'ended_at' => $session->ended_at ?: now(),
            ]);
        });
    }

    private function generateAccessCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (LiveQuizSession::query()->where('access_code', $code)->exists());

        return $code;
    }

    private function assertLectureContext(Module $module, ModuleSection $section, ModuleLecture $lecture): void
    {
        abort_unless((int) $lecture->module_id === (int) $module->id, 404);
        abort_unless((int) $lecture->section_id === (int) $section->id, 404);
    }

    private function assertSessionContext(LiveQuizSession $session, Module $module, ModuleSection $section, ModuleLecture $lecture): void
    {
        $this->assertLectureContext($module, $section, $lecture);
        abort_unless((int) $session->module_id === (int) $module->id, 404);
        abort_unless((int) $session->section_id === (int) $section->id, 404);
        abort_unless((int) $session->lecture_id === (int) $lecture->id, 404);
        abort_if((int) $session->formateur_id !== (int) auth()->id(), 403);
    }

    private function createSessionAndRedirect(
        Module $module,
        ModuleSection $section,
        ModuleLecture $lecture,
        ?Group $group = null
    ): RedirectResponse {
        $questions = QuizQuestion::query()
            ->where('lecture_id', $lecture->id)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($questions->isEmpty()) {
            return back()->withErrors([
                'live_quiz' => 'Ajoutez au moins une question active sur cette lecon avant de lancer un quiz en direct.',
            ]);
        }

        $existingSessions = LiveQuizSession::query()
            ->where('formateur_id', auth()->id())
            ->where('lecture_id', $lecture->id)
            ->where('group_id', $group?->id)
            ->whereNull('ended_at')
            ->get();

        foreach ($existingSessions as $existingSession) {
            $this->closeSession($existingSession);
        }

        $session = DB::transaction(function () use ($module, $section, $lecture, $questions, $group): LiveQuizSession {
            $session = LiveQuizSession::query()->create([
                'formateur_id' => auth()->id(),
                'group_id' => $group?->id,
                'module_id' => $module->id,
                'section_id' => $section->id,
                'lecture_id' => $lecture->id,
                'access_code' => $this->generateAccessCode(),
                'status' => LiveQuizSession::STATUS_WAITING,
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
            ->route('formateur.live-quiz.show', [
                'module' => $module->id,
                'section' => $section->id,
                'lecture' => $lecture->id,
                'session' => $session->id,
            ])
            ->with('success', 'Le quiz en direct est pret. Les stagiaires peuvent maintenant rejoindre avec le QR code.');
    }

    private function extractAnswerOptionIds(mixed $rawValue): array
    {
        if (is_array($rawValue)) {
            return collect($rawValue)->map(fn ($value) => (int) $value)->filter()->values()->all();
        }

        if (! is_string($rawValue) || trim($rawValue) === '') {
            return [];
        }

        $decoded = json_decode($rawValue, true);
        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)->map(fn ($value) => (int) $value)->filter()->values()->all();
    }

    private function statusLabel(LiveQuizSession $session): string
    {
        return match ((string) $session->status) {
            LiveQuizSession::STATUS_WAITING => 'En attente',
            LiveQuizSession::STATUS_QUESTION_OPEN => 'Question ouverte',
            LiveQuizSession::STATUS_ANSWER_REVEALED => 'Correction affichee',
            LiveQuizSession::STATUS_CLOSED => 'Terminee',
            default => 'Session',
        };
    }
}
