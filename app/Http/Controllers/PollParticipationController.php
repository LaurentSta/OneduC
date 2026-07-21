<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\PollSession;
use App\Models\PollSessionResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PollParticipationController extends Controller
{
    public function home(): View
    {
        return view('sondages.join');
    }

    public function resolveCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:12'],
        ]);

        return redirect()->route('sondages.join.code', ['code' => strtoupper(trim($data['code']))]);
    }

    public function joinByCode(string $code): View
    {
        $pollSession = PollSession::query()
            ->where('access_code', strtoupper(trim($code)))
            ->with(['group', 'questionnaire'])
            ->firstOrFail();

        $this->ensureCanParticipate($pollSession, auth()->user());

        $myResponses = PollSessionResponse::query()
            ->where('poll_session_id', $pollSession->id)
            ->where('user_id', (int) auth()->id())
            ->pluck('choice_index', 'question_index');

        return view('sondages.show', [
            'pollSession' => $pollSession,
            'myResponses' => $myResponses,
        ]);
    }

    public function submit(Request $request, string $code): RedirectResponse
    {
        $pollSession = PollSession::query()
            ->where('access_code', strtoupper(trim($code)))
            ->with('questionnaire')
            ->firstOrFail();

        $this->ensureCanParticipate($pollSession, $request->user());

        if (! $pollSession->is_active) {
            return back()->withErrors(['choice_index' => 'Ce sondage est fermé pour le moment.']);
        }

        $questions = collect($pollSession->questions ?? [])->values();

        $data = $request->validate([
            'question_index' => ['required', 'integer', 'min:0', 'max:' . max(0, $questions->count() - 1)],
            'choice_index' => ['required', 'integer', 'min:0'],
        ]);

        $questionIndex = (int) $data['question_index'];
        $choiceIndex = (int) $data['choice_index'];

        $choices = collect($questions[$questionIndex]['choices'] ?? [])->values();
        if (! $choices->has($choiceIndex)) {
            return back()->withErrors(['choice_index' => 'Choix invalide.'])->withInput();
        }

        PollSessionResponse::query()->updateOrCreate(
            [
                'poll_session_id' => $pollSession->id,
                'user_id' => (int) auth()->id(),
                'question_index' => $questionIndex,
            ],
            [
                'choice_index' => $choiceIndex,
            ]
        );

        return back()->with('success', true)->with('answered_qi', $questionIndex);
    }

    public function data(string $code): JsonResponse
    {
        $pollSession = PollSession::query()
            ->where('access_code', strtoupper(trim($code)))
            ->with('questionnaire')
            ->firstOrFail();

        $this->ensureCanParticipate($pollSession, auth()->user());

        $questions = collect($pollSession->questions ?? [])->values();

        $voteRows = PollSessionResponse::query()
            ->where('poll_session_id', $pollSession->id)
            ->select('question_index', 'choice_index', DB::raw('count(*) as votes'))
            ->groupBy('question_index', 'choice_index')
            ->get();

        $respondentsByQuestion = PollSessionResponse::query()
            ->where('poll_session_id', $pollSession->id)
            ->select('question_index', DB::raw('count(distinct user_id) as respondents'))
            ->groupBy('question_index')
            ->pluck('respondents', 'question_index');

        $myResponses = PollSessionResponse::query()
            ->where('poll_session_id', $pollSession->id)
            ->where('user_id', (int) auth()->id())
            ->pluck('choice_index', 'question_index');

        $questionsPayload = $questions->map(function ($question, $qi) use ($voteRows, $respondentsByQuestion, $myResponses) {
            $choices = collect($question['choices'] ?? [])->values();
            $totalVotes = (int) $voteRows->where('question_index', $qi)->sum('votes');

            return [
                'question' => (string) ($question['question'] ?? ''),
                'respondents' => (int) ($respondentsByQuestion[$qi] ?? 0),
                'my_choice_index' => $myResponses->has($qi) ? (int) $myResponses[$qi] : null,
                'choices' => $choices->map(function ($choiceLabel, $ci) use ($voteRows, $qi, $totalVotes) {
                    $votes = (int) optional(
                        $voteRows
                            ->where('question_index', $qi)
                            ->firstWhere('choice_index', $ci)
                    )->votes;

                    $percent = $totalVotes > 0 ? (int) round(($votes / $totalVotes) * 100) : 0;

                    return [
                        'label' => (string) $choiceLabel,
                        'votes' => $votes,
                        'percent' => $percent,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return response()->json([
            'is_active' => (bool) $pollSession->is_active,
            'questions' => $questionsPayload,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    private function ensureCanParticipate(PollSession $pollSession, ?User $user): void
    {
        abort_unless($user instanceof User, 403);

        $formateurId = (int) $pollSession->formateur_id;
        $userId = (int) $user->id;

        if ($userId === $formateurId) {
            return;
        }

        $isAllowed = Group::query()
            ->accessibleByTrainer($formateurId)
            ->when($pollSession->group_id, fn ($query) => $query->where('id', $pollSession->group_id))
            ->whereHas('users', fn ($query) => $query->where('users.id', $userId))
            ->exists();

        abort_unless($isAllowed, 403);
    }
}
