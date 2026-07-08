<?php

namespace App\Http\Controllers;

use App\Models\BuzzerSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuzzerParticipationController extends Controller
{
    public function home(): View
    {
        return view('buzzer.join');
    }

    public function resolveCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:12'],
        ]);

        return redirect()->route('buzzer.join.code', ['code' => $this->normalizeCode($data['code'])]);
    }

    public function joinByCode(string $code): View
    {
        $session = $this->findSessionByCode($code);
        $this->ensureCanView($session, auth()->user());

        return view('buzzer.show', [
            'session' => $session,
            'snapshot' => $this->buildSnapshot($session, (int) auth()->id()),
        ]);
    }

    public function buzz(Request $request, string $code): JsonResponse
    {
        $session = $this->findSessionByCode($code);
        $this->ensureCanAnswer($session, $request->user());

        abort_if($session->status !== BuzzerSession::STATUS_QUESTION_OPEN, 422, 'Le buzzer n\'est pas ouvert.');

        $question = $session->currentQuestion();
        abort_if(! $question, 404);

        $reactionMs = $question->opened_at
            ? (int) round(abs($question->opened_at->diffInMilliseconds(now())))
            : 0;

        $question->attempts()->firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'buzzer_session_id' => $session->id,
                'reaction_ms' => $reactionMs,
            ]
        );

        $session->participants()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['score' => 0]
        );

        return response()->json($this->buildSnapshot($session->refresh(), (int) $request->user()->id));
    }

    public function snapshot(Request $request, string $code): JsonResponse
    {
        $session = $this->findSessionByCode($code);
        $this->ensureCanView($session, $request->user());

        return response()->json($this->buildSnapshot($session, (int) $request->user()->id));
    }

    private function buildSnapshot(BuzzerSession $session, int $userId): array
    {
        $question = $session->currentQuestion();
        $holder = $question?->currentHolder();
        $winner = $question?->attempts()->where('result', 'correct')->with('user:id,name,prenom')->first();

        $myAttempt = $question
            ? $question->attempts()->where('user_id', $userId)->first()
            : null;

        $leaderboard = $session->participants()
            ->with('user:id,name,prenom')
            ->orderByDesc('score')
            ->get()
            ->map(fn ($participant) => [
                'name' => $participant->user?->name ?? '—',
                'score' => $participant->score,
            ]);

        return [
            'state_key' => implode('|', [
                $session->status,
                $session->current_position,
                $holder?->id ?? 0,
                $holder?->result ?? '',
                $myAttempt?->id ?? 0,
                $myAttempt?->result ?? '',
            ]),
            'status' => $session->status,
            'mode' => $session->mode,
            'current_position' => $session->current_position,
            'total_questions' => $session->total_questions,
            'question_text' => $session->mode === BuzzerSession::MODE_DISTANCE ? $question?->question_text : null,
            'has_buzzed' => (bool) $myAttempt,
            'my_result' => $myAttempt?->result,
            'is_holder' => $holder && $myAttempt && (int) $holder->id === (int) $myAttempt->id,
            'holder_name' => $holder?->user?->name,
            'winner_name' => $winner?->user?->name,
            'leaderboard' => $leaderboard,
        ];
    }

    private function findSessionByCode(string $code): BuzzerSession
    {
        return BuzzerSession::query()
            ->where('access_code', $this->normalizeCode($code))
            ->with(['group', 'questions'])
            ->firstOrFail();
    }

    private function ensureCanView(BuzzerSession $session, ?User $user): void
    {
        abort_unless($user instanceof User, 403);

        $group = $session->group;
        abort_unless($group, 404);

        $isOwner = (int) $group->instructor_id === (int) $user->id;
        $isCoTrainer = $group->coFormateurs()->where('users.id', (int) $user->id)->exists();
        $isMember = $group->users()->where('users.id', (int) $user->id)->exists();

        abort_unless($isOwner || $isCoTrainer || $isMember, 403);
    }

    private function ensureCanAnswer(BuzzerSession $session, ?User $user): void
    {
        abort_unless($user instanceof User, 403);

        $group = $session->group;
        abort_unless($group, 404);

        abort_unless(
            $group->students()->where('users.id', (int) $user->id)->exists(),
            403
        );
    }

    private function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }
}
