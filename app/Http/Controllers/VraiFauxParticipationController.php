<?php

namespace App\Http\Controllers;

use App\Models\TrueFalseSession;
use App\Models\TrueFalseSessionResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VraiFauxParticipationController extends Controller
{
    public function home(): View
    {
        return view('vraifaux.join');
    }

    public function resolveCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:12'],
        ]);

        return redirect()->route('vraifaux.join.code', ['code' => $this->normalizeCode($data['code'])]);
    }

    public function joinByCode(string $code): View
    {
        $trueFalseSession = TrueFalseSession::query()
            ->where('access_code', $this->normalizeCode($code))
            ->with('group')
            ->firstOrFail();

        $this->ensureCanView($trueFalseSession, auth()->user());

        return view('vraifaux.show', [
            'trueFalseSession' => $trueFalseSession,
            'myResponses' => $this->myResponses($trueFalseSession),
        ]);
    }

    public function submit(Request $request, string $code): RedirectResponse
    {
        $trueFalseSession = TrueFalseSession::query()
            ->where('access_code', $this->normalizeCode($code))
            ->with('group')
            ->firstOrFail();

        $this->ensureCanAnswer($trueFalseSession, $request->user());

        if (! $trueFalseSession->is_active) {
            return back()->withErrors(['answer' => 'Ce questionnaire est fermé pour le moment.']);
        }

        $questions = collect($trueFalseSession->questions ?? [])->values();

        $data = $request->validate([
            'question_index' => ['required', 'integer', 'min:0', 'max:'.max(0, $questions->count() - 1)],
            'answer' => ['required', 'in:0,1,true,false'],
        ]);

        $questionIndex = (int) $data['question_index'];

        TrueFalseSessionResponse::query()->updateOrCreate(
            [
                'true_false_session_id' => $trueFalseSession->id,
                'user_id' => (int) auth()->id(),
                'question_index' => $questionIndex,
            ],
            [
                'answer' => (bool) filter_var($data['answer'], FILTER_VALIDATE_BOOLEAN),
            ]
        );

        return back()->with('success', true)->with('answered_qi', $questionIndex);
    }

    public function data(string $code): JsonResponse
    {
        $trueFalseSession = TrueFalseSession::query()
            ->where('access_code', $this->normalizeCode($code))
            ->with('group')
            ->firstOrFail();

        $this->ensureCanView($trueFalseSession, auth()->user());

        $questions = collect($trueFalseSession->questions ?? [])->values();
        $myResponses = $this->myResponses($trueFalseSession);

        $voteRows = TrueFalseSessionResponse::query()
            ->where('true_false_session_id', $trueFalseSession->id)
            ->select('question_index', 'answer', DB::raw('count(*) as votes'))
            ->groupBy('question_index', 'answer')
            ->get();

        $respondentsByQuestion = TrueFalseSessionResponse::query()
            ->where('true_false_session_id', $trueFalseSession->id)
            ->select('question_index', DB::raw('count(distinct user_id) as respondents'))
            ->groupBy('question_index')
            ->pluck('respondents', 'question_index');

        $questionsPayload = $questions->map(function ($question, $questionIndex) use ($voteRows, $respondentsByQuestion, $myResponses) {
            $rows = $voteRows->where('question_index', $questionIndex);
            $totalVotes = (int) $rows->sum('votes');
            $trueVotes = (int) optional($rows->first(fn ($row): bool => (bool) $row->answer === true))->votes;
            $falseVotes = (int) optional($rows->first(fn ($row): bool => (bool) $row->answer === false))->votes;
            $hasAnswered = $myResponses->has($questionIndex);

            return [
                'statement' => (string) ($question['statement'] ?? ''),
                'respondents' => (int) ($respondentsByQuestion[$questionIndex] ?? 0),
                'my_answer' => $hasAnswered ? (bool) $myResponses[$questionIndex] : null,
                'correct_answer' => $hasAnswered ? (bool) ($question['answer'] ?? false) : null,
                'true' => [
                    'votes' => $trueVotes,
                    'percent' => $totalVotes > 0 ? (int) round(($trueVotes / $totalVotes) * 100) : 0,
                ],
                'false' => [
                    'votes' => $falseVotes,
                    'percent' => $totalVotes > 0 ? (int) round(($falseVotes / $totalVotes) * 100) : 0,
                ],
            ];
        })->values()->all();

        return response()->json([
            'is_active' => (bool) $trueFalseSession->is_active,
            'questions' => $questionsPayload,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    private function ensureCanView(TrueFalseSession $trueFalseSession, ?User $user): void
    {
        abort_unless($user instanceof User, 403);

        $group = $trueFalseSession->group;
        abort_unless($group, 404);

        $isTrainer = $user->role === 'formateur' && (
            (int) $group->instructor_id === (int) $user->id
            || $group->coFormateurs()->where('users.id', (int) $user->id)->exists()
        );

        $isStudent = $user->role === 'stagiaire'
            && (bool) $group->is_active
            && $group->students()->where('users.id', (int) $user->id)->exists();

        abort_unless($isTrainer || $isStudent, 403);
    }

    private function ensureCanAnswer(TrueFalseSession $trueFalseSession, ?User $user): void
    {
        abort_unless($user instanceof User && $user->role === 'stagiaire', 403);

        $group = $trueFalseSession->group;
        abort_unless($group && (bool) $group->is_active, 404);

        $isMember = $group->students()
            ->where('users.id', (int) $user->id)
            ->exists();

        abort_unless($isMember, 403);
    }

    private function myResponses(TrueFalseSession $trueFalseSession): \Illuminate\Support\Collection
    {
        return TrueFalseSessionResponse::query()
            ->where('true_false_session_id', $trueFalseSession->id)
            ->where('user_id', (int) auth()->id())
            ->get(['question_index', 'answer'])
            ->mapWithKeys(fn (TrueFalseSessionResponse $response): array => [
                (int) $response->question_index => (bool) $response->answer,
            ]);
    }

    private function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }
}
