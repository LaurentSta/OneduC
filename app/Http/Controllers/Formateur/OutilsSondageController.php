<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\PollSession;
use App\Models\PollSessionResponse;
use App\Services\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OutilsSondageController extends Controller
{
    public function index(): View
    {
        $formateurId = (int) auth()->id();

        $groups = Group::query()
            ->accessibleByTrainer($formateurId)
            ->withCount('students')
            ->orderBy('name')
            ->get(['id', 'name']);

        $sessions = PollSession::query()
            ->where('formateur_id', $formateurId)
            ->with('group')
            ->withCount('responses')
            ->latest()
            ->limit(20)
            ->get();

        return view('formateur.outils.sondages_index', compact('groups', 'sessions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $formateurId = (int) auth()->id();

        $data = $request->validate([
            'group_id' => ['nullable', 'exists:groups,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'questions' => ['required', 'array', 'min:1', 'max:10'],
            'questions.*.question' => ['required', 'string', 'max:500'],
            'questions.*.choices' => ['required', 'array', 'min:2', 'max:5'],
            'questions.*.choices.*' => ['required', 'string', 'max:200'],
        ]);

        $group = null;
        if (! empty($data['group_id'])) {
            $group = Group::query()
                ->accessibleByTrainer($formateurId)
                ->findOrFail((int) $data['group_id']);
        }

        $questions = collect($data['questions'])
            ->map(function (array $question): array {
                return [
                    'question' => trim((string) $question['question']),
                    'choices' => collect($question['choices'])
                        ->map(fn ($choice) => trim((string) $choice))
                        ->filter()
                        ->values()
                        ->all(),
                ];
            })
            ->filter(fn (array $question): bool => $question['question'] !== '' && count($question['choices']) >= 2)
            ->values();

        abort_if($questions->isEmpty(), 422, 'Le sondage doit contenir au moins une question valide.');

        $session = PollSession::query()->create([
            'formateur_id' => $formateurId,
            'group_id' => $group?->id,
            'title' => trim((string) ($data['title'] ?? '')) ?: 'Sondage',
            'questions' => $questions->all(),
            'access_code' => CodeGeneratorService::generateUniqueCode(PollSession::class),
            'is_active' => (bool) $group,
            'opened_at' => $group ? now() : null,
            'closed_at' => null,
        ]);

        if ($group) {
            return redirect()->route('formateur.sondages.show', $session);
        }

        return redirect()
            ->route('formateur.sondages.index')
            ->with('success', "Sondage créé. Vous pouvez l'utiliser dans un parcours ou le lancer plus tard pour un groupe.");
    }

    public function show(PollSession $pollSession): View
    {
        $this->assertOwnership($pollSession);
        $pollSession->load('group');

        $stats = $this->buildStatsPayload($pollSession);

        return view('formateur.outils.sondages_show', [
            'pollSession' => $pollSession,
            'joinUrl' => route('sondages.join.code', $pollSession->access_code),
            'stats' => $stats,
        ]);
    }

    public function toggle(PollSession $pollSession): RedirectResponse
    {
        $this->assertOwnership($pollSession);

        $newStatus = ! $pollSession->is_active;

        $pollSession->update([
            'is_active' => $newStatus,
            'opened_at' => $newStatus ? now() : $pollSession->opened_at,
            'closed_at' => $newStatus ? null : now(),
        ]);

        return back()->with(
            'success',
            $newStatus ? 'Le sondage est maintenant ouvert.' : 'Le sondage est maintenant fermé.'
        );
    }

    public function state(PollSession $pollSession): JsonResponse
    {
        $this->assertOwnership($pollSession);

        return response()->json($this->buildStatsPayload($pollSession));
    }

    private function buildStatsPayload(PollSession $pollSession): array
    {
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

        $totalRespondents = PollSessionResponse::query()
            ->where('poll_session_id', $pollSession->id)
            ->distinct('user_id')
            ->count('user_id');

        $questionsPayload = $questions->map(function ($question, $qi) use ($voteRows, $respondentsByQuestion) {
            $choices = collect($question['choices'] ?? [])->values();
            $totalVotes = (int) $voteRows
                ->where('question_index', $qi)
                ->sum('votes');

            return [
                'question' => (string) ($question['question'] ?? ''),
                'respondents' => (int) ($respondentsByQuestion[$qi] ?? 0),
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

        return [
            'is_active' => (bool) $pollSession->is_active,
            'respondents_total' => (int) $totalRespondents,
            'questions' => $questionsPayload,
            'updated_at' => now()->toIso8601String(),
        ];
    }

    private function assertOwnership(PollSession $pollSession): void
    {
        abort_unless((int) $pollSession->formateur_id === (int) auth()->id(), 403);
    }
}
