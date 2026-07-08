<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\BuzzerSession;
use App\Models\Group;
use App\Services\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OutilsBuzzerController extends Controller
{
    public function index(): View
    {
        $formateurId = (int) auth()->id();

        $groups = Group::query()
            ->accessibleByTrainer($formateurId)
            ->withCount('students')
            ->orderBy('name')
            ->get(['id', 'name']);

        $sessions = BuzzerSession::query()
            ->whereHas('group', fn ($query) => $query->accessibleByTrainer($formateurId))
            ->with('group:id,name')
            ->withCount('participants')
            ->latest()
            ->limit(20)
            ->get();

        return view('formateur.outils.buzzer_index', compact('groups', 'sessions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $formateurId = (int) auth()->id();

        $data = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'mode' => ['required', 'in:presentiel,distance'],
            'questions' => ['required', 'array', 'min:2', 'max:30'],
            'questions.*' => ['required', 'string', 'max:500'],
        ]);

        $group = Group::query()
            ->accessibleByTrainer($formateurId)
            ->findOrFail((int) $data['group_id']);

        $questions = collect($data['questions'])
            ->map(fn ($text) => trim((string) $text))
            ->filter(fn ($text) => $text !== '')
            ->values();

        abort_if($questions->count() < 2, 422, 'Ajoutez au moins deux questions pour lancer le jeu.');

        $session = DB::transaction(function () use ($formateurId, $group, $data, $questions): BuzzerSession {
            $session = BuzzerSession::query()->create([
                'formateur_id' => $formateurId,
                'group_id' => $group->id,
                'title' => trim((string) ($data['title'] ?? '')) ?: 'Buzzer Quiz',
                'mode' => $data['mode'],
                'access_code' => CodeGeneratorService::generateUniqueCode(BuzzerSession::class),
                'status' => BuzzerSession::STATUS_WAITING,
                'current_position' => 0,
                'total_questions' => $questions->count(),
            ]);

            foreach ($questions as $index => $text) {
                $session->questions()->create([
                    'position' => $index + 1,
                    'question_text' => $text,
                ]);
            }

            return $session;
        });

        return redirect()->route('formateur.buzzer.show', $session);
    }

    public function show(BuzzerSession $buzzerSession): View
    {
        $this->assertAccess($buzzerSession);
        $buzzerSession->load(['group', 'questions', 'participants.user']);

        return view('formateur.outils.buzzer_show', [
            'session' => $buzzerSession,
            'joinUrl' => route('buzzer.join.code', $buzzerSession->access_code),
            'snapshot' => $this->buildSnapshot($buzzerSession),
        ]);
    }

    public function snapshot(BuzzerSession $buzzerSession): JsonResponse
    {
        $this->assertAccess($buzzerSession);
        $buzzerSession->load(['questions', 'participants.user']);

        return response()->json($this->buildSnapshot($buzzerSession));
    }

    public function start(BuzzerSession $buzzerSession): RedirectResponse
    {
        $this->assertAccess($buzzerSession);
        abort_if($buzzerSession->isClosed(), 403);

        $firstQuestion = $buzzerSession->questions()->where('position', 1)->firstOrFail();
        $firstQuestion->update(['opened_at' => now()]);

        $buzzerSession->update([
            'status' => BuzzerSession::STATUS_QUESTION_OPEN,
            'current_position' => 1,
            'opened_at' => $buzzerSession->opened_at ?: now(),
        ]);

        return back()->with('success', 'Le jeu est lancé. La première question est ouverte.');
    }

    public function correct(BuzzerSession $buzzerSession): RedirectResponse
    {
        $this->assertAccess($buzzerSession);
        abort_if($buzzerSession->isClosed(), 403);

        $question = $buzzerSession->currentQuestion();
        abort_if(! $question, 404);

        $holder = $question->currentHolder();
        abort_if(! $holder, 422, 'Personne n\'a buzzé pour le moment.');

        DB::transaction(function () use ($buzzerSession, $holder): void {
            $holder->update(['result' => 'correct']);

            $participant = $buzzerSession->participants()->firstOrCreate(
                ['user_id' => $holder->user_id],
                ['score' => 0]
            );
            $participant->increment('score', 100);
        });

        $buzzerSession->update(['status' => BuzzerSession::STATUS_REVEALED]);

        return back()->with('success', 'Bonne réponse enregistrée.');
    }

    public function incorrect(BuzzerSession $buzzerSession): RedirectResponse
    {
        $this->assertAccess($buzzerSession);
        abort_if($buzzerSession->isClosed(), 403);

        $question = $buzzerSession->currentQuestion();
        abort_if(! $question, 404);

        $holder = $question->currentHolder();
        abort_if(! $holder, 422, 'Personne n\'a buzzé pour le moment.');

        $holder->update(['result' => 'incorrect']);

        return back()->with('success', 'Mauvaise réponse enregistrée, le buzzer reste ouvert.');
    }

    public function skip(BuzzerSession $buzzerSession): RedirectResponse
    {
        $this->assertAccess($buzzerSession);
        abort_if($buzzerSession->isClosed(), 403);

        $buzzerSession->update(['status' => BuzzerSession::STATUS_REVEALED]);

        return back()->with('success', 'Question passée.');
    }

    public function next(BuzzerSession $buzzerSession): RedirectResponse
    {
        $this->assertAccess($buzzerSession);
        abort_if($buzzerSession->isClosed(), 403);

        $nextPosition = $buzzerSession->current_position + 1;

        if ($nextPosition > $buzzerSession->total_questions) {
            $buzzerSession->update([
                'status' => BuzzerSession::STATUS_CLOSED,
                'ended_at' => now(),
            ]);

            return back()->with('success', 'Le jeu est terminé.');
        }

        $nextQuestion = $buzzerSession->questions()->where('position', $nextPosition)->firstOrFail();
        $nextQuestion->update(['opened_at' => now()]);

        $buzzerSession->update([
            'status' => BuzzerSession::STATUS_QUESTION_OPEN,
            'current_position' => $nextPosition,
        ]);

        return back()->with('success', 'Question suivante envoyée.');
    }

    public function close(BuzzerSession $buzzerSession): RedirectResponse
    {
        $this->assertAccess($buzzerSession);

        $buzzerSession->update([
            'status' => BuzzerSession::STATUS_CLOSED,
            'ended_at' => $buzzerSession->ended_at ?: now(),
        ]);

        return back()->with('success', 'Le jeu a été clôturé.');
    }

    public function destroy(BuzzerSession $buzzerSession): RedirectResponse
    {
        $this->assertAccess($buzzerSession);

        $buzzerSession->delete();

        return redirect()
            ->route('formateur.buzzer.index')
            ->with('success', 'Le jeu a été supprimé.');
    }

    private function buildSnapshot(BuzzerSession $buzzerSession): array
    {
        $question = $buzzerSession->currentQuestion();
        $holder = $question?->currentHolder();
        $winner = $question?->attempts()->where('result', 'correct')->with('user:id,name,prenom')->first();

        $leaderboard = $buzzerSession->participants
            ->sortByDesc('score')
            ->values()
            ->map(fn ($participant) => [
                'name' => $participant->user?->name ?? '—',
                'score' => $participant->score,
            ]);

        return [
            'state_key' => implode('|', [
                $buzzerSession->status,
                $buzzerSession->current_position,
                $holder?->id ?? 0,
                $holder?->result ?? '',
            ]),
            'status' => $buzzerSession->status,
            'mode' => $buzzerSession->mode,
            'current_position' => $buzzerSession->current_position,
            'total_questions' => $buzzerSession->total_questions,
            'question_text' => $question?->question_text,
            'holder_name' => $holder?->user?->name,
            'holder_reaction_ms' => $holder?->reaction_ms,
            'winner_name' => $winner?->user?->name,
            'leaderboard' => $leaderboard,
        ];
    }

    private function assertAccess(BuzzerSession $buzzerSession): void
    {
        abort_unless(
            $buzzerSession->group()
                ->accessibleByTrainer((int) auth()->id())
                ->exists(),
            403
        );
    }
}
