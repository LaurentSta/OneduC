<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\TrueFalseSession;
use App\Models\TrueFalseSessionResponse;
use App\Services\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OutilsVraiFauxController extends Controller
{
    public function index(): View
    {
        $formateurId = (int) auth()->id();

        $groups = Group::query()
            ->accessibleByTrainer($formateurId)
            ->withCount('students')
            ->orderBy('name')
            ->get(['id', 'name']);

        $sessions = TrueFalseSession::query()
            ->whereHas('group', fn ($query) => $query->accessibleByTrainer($formateurId))
            ->with('group')
            ->withCount('responses')
            ->latest()
            ->limit(20)
            ->get();

        return view('formateur.outils.vraifaux_index', compact('groups', 'sessions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $formateurId = (int) auth()->id();

        $data = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'questions' => ['required', 'array', 'min:1', 'max:20'],
            'questions.*.statement' => ['required', 'string', 'max:500'],
            'questions.*.answer' => ['required', 'in:0,1,true,false'],
            'questions.*.explanation' => ['nullable', 'string', 'max:1000'],
        ]);

        $group = Group::query()
            ->accessibleByTrainer($formateurId)
            ->findOrFail((int) $data['group_id']);

        $questions = collect($data['questions'])
            ->map(function (array $question): array {
                return [
                    'statement' => trim((string) $question['statement']),
                    'answer' => (bool) filter_var($question['answer'], FILTER_VALIDATE_BOOLEAN),
                    'explanation' => trim((string) ($question['explanation'] ?? '')) ?: null,
                ];
            })
            ->filter(fn (array $question): bool => $question['statement'] !== '')
            ->values();

        abort_if($questions->isEmpty(), 422, 'Le questionnaire doit contenir au moins une affirmation valide.');

        $session = TrueFalseSession::query()->create([
            'formateur_id' => $formateurId,
            'group_id' => $group->id,
            'title' => trim((string) ($data['title'] ?? '')) ?: 'Vrai ou Faux',
            'questions' => $questions->all(),
            'access_code' => CodeGeneratorService::generateUniqueCode(TrueFalseSession::class),
            'is_active' => true,
            'opened_at' => now(),
            'closed_at' => null,
        ]);

        return redirect()->route('formateur.vraifaux.show', $session);
    }

    public function show(TrueFalseSession $trueFalseSession): View
    {
        $this->assertTrainerAccess($trueFalseSession);
        $trueFalseSession->load('group');

        return view('formateur.outils.vraifaux_show', [
            'trueFalseSession' => $trueFalseSession,
            'joinUrl' => route('vraifaux.join.code', $trueFalseSession->access_code),
            'stats' => $this->buildStatsPayload($trueFalseSession),
        ]);
    }

    public function toggle(TrueFalseSession $trueFalseSession): RedirectResponse
    {
        $this->assertTrainerAccess($trueFalseSession);

        $newStatus = ! $trueFalseSession->is_active;

        $trueFalseSession->update([
            'is_active' => $newStatus,
            'opened_at' => $newStatus ? now() : $trueFalseSession->opened_at,
            'closed_at' => $newStatus ? null : now(),
        ]);

        return back()->with(
            'success',
            $newStatus ? 'Le questionnaire est maintenant ouvert.' : 'Le questionnaire est maintenant fermé.'
        );
    }

    public function state(TrueFalseSession $trueFalseSession): JsonResponse
    {
        $this->assertTrainerAccess($trueFalseSession);

        return response()->json($this->buildStatsPayload($trueFalseSession));
    }

    private function buildStatsPayload(TrueFalseSession $trueFalseSession): array
    {
        $questions = collect($trueFalseSession->questions ?? [])->values();

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

        $totalRespondents = TrueFalseSessionResponse::query()
            ->where('true_false_session_id', $trueFalseSession->id)
            ->distinct('user_id')
            ->count('user_id');

        $questionsPayload = $questions->map(function ($question, $questionIndex) use ($voteRows, $respondentsByQuestion) {
            $rows = $voteRows->where('question_index', $questionIndex);
            $totalVotes = (int) $rows->sum('votes');
            $trueVotes = (int) optional($rows->first(fn ($row): bool => (bool) $row->answer === true))->votes;
            $falseVotes = (int) optional($rows->first(fn ($row): bool => (bool) $row->answer === false))->votes;
            $correctAnswer = (bool) ($question['answer'] ?? false);
            $correctVotes = $correctAnswer ? $trueVotes : $falseVotes;

            return [
                'statement' => (string) ($question['statement'] ?? ''),
                'answer' => $correctAnswer,
                'respondents' => (int) ($respondentsByQuestion[$questionIndex] ?? 0),
                'correct_count' => $correctVotes,
                'correct_percent' => $totalVotes > 0 ? (int) round(($correctVotes / $totalVotes) * 100) : 0,
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

        return [
            'is_active' => (bool) $trueFalseSession->is_active,
            'respondents_total' => (int) $totalRespondents,
            'questions' => $questionsPayload,
            'updated_at' => now()->toIso8601String(),
        ];
    }

    private function assertTrainerAccess(TrueFalseSession $trueFalseSession): void
    {
        $formateurId = (int) auth()->id();

        abort_unless(
            Group::query()
                ->accessibleByTrainer($formateurId)
                ->whereKey($trueFalseSession->group_id)
                ->exists(),
            403
        );
    }
}
