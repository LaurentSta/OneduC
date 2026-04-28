<?php

namespace App\Http\Controllers;

use App\Models\ScaleSession;
use App\Models\ScaleSessionResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScaleParticipationController extends Controller
{
    public function home(): View
    {
        return view('echelle.join');
    }

    public function resolveCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:12'],
        ]);

        return redirect()->route('echelle.join.code', ['code' => strtoupper(trim($data['code']))]);
    }

    public function joinByCode(string $code): View
    {
        $session = ScaleSession::query()
            ->where('access_code', strtoupper(trim($code)))
            ->with('group')
            ->firstOrFail();

        $this->ensureCanParticipate($session, auth()->user());

        $myResponses = ScaleSessionResponse::query()
            ->where('scale_session_id', $session->id)
            ->where('user_id', (int) auth()->id())
            ->pluck('value', 'question_index');

        return view('echelle.show', [
            'scaleSession' => $session,
            'myResponses'  => $myResponses,
        ]);
    }

    public function submit(Request $request, string $code): RedirectResponse
    {
        $session = ScaleSession::query()
            ->where('access_code', strtoupper(trim($code)))
            ->firstOrFail();

        $this->ensureCanParticipate($session, $request->user());

        if (! $session->is_active) {
            return back()->withErrors(['value' => 'Cette échelle est fermée pour le moment.']);
        }

        $questions = collect($session->questions ?? [])->values();

        $data = $request->validate([
            'question_index' => ['required', 'integer', 'min:0', 'max:' . max(0, $questions->count() - 1)],
            'value'          => ['required', 'integer'],
        ]);

        $qi       = (int) $data['question_index'];
        $question = $questions[$qi] ?? null;

        abort_unless($question !== null, 422);

        $min   = (int) ($question['min'] ?? 1);
        $max   = (int) ($question['max'] ?? 10);
        $value = max($min, min($max, (int) $data['value']));

        ScaleSessionResponse::query()->updateOrCreate(
            [
                'scale_session_id' => $session->id,
                'user_id'          => (int) auth()->id(),
                'question_index'   => $qi,
            ],
            ['value' => $value]
        );

        return back()->with('success', true)->with('answered_qi', $qi);
    }

    public function data(string $code): JsonResponse
    {
        $session = ScaleSession::query()
            ->where('access_code', strtoupper(trim($code)))
            ->firstOrFail();

        $this->ensureCanParticipate($session, auth()->user());

        $questions = collect($session->questions ?? [])->values();

        $myResponses = ScaleSessionResponse::query()
            ->where('scale_session_id', $session->id)
            ->where('user_id', (int) auth()->id())
            ->pluck('value', 'question_index');

        $questionsPayload = $questions->map(function ($question, $qi) use ($session, $myResponses) {
            $min = (int) ($question['min'] ?? 1);
            $max = (int) ($question['max'] ?? 10);

            $values      = ScaleSessionResponse::query()
                ->where('scale_session_id', $session->id)
                ->where('question_index', $qi)
                ->pluck('value')
                ->map(fn ($v) => (int) $v);

            $respondents = $values->count();
            $average     = $respondents > 0 ? round($values->avg(), 1) : null;

            $countsByValue = $values->countBy()->all();

            $distribution = collect(range($min, $max))->map(function (int $v) use ($countsByValue, $respondents): array {
                $count   = (int) ($countsByValue[$v] ?? 0);
                $percent = $respondents > 0 ? (int) round(($count / $respondents) * 100) : 0;
                return ['value' => $v, 'count' => $count, 'percent' => $percent];
            })->values()->all();

            return [
                'question'     => (string) ($question['question'] ?? ''),
                'label_min'    => (string) ($question['label_min'] ?? ''),
                'label_max'    => (string) ($question['label_max'] ?? ''),
                'min'          => $min,
                'max'          => $max,
                'respondents'  => $respondents,
                'average'      => $average,
                'my_value'     => $myResponses->has($qi) ? (int) $myResponses[$qi] : null,
                'distribution' => $distribution,
            ];
        })->values()->all();

        return response()->json([
            'is_active'  => (bool) $session->is_active,
            'questions'  => $questionsPayload,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    private function ensureCanParticipate(ScaleSession $session, ?User $user): void
    {
        abort_unless($user instanceof User, 403);

        $group = $session->group;
        abort_unless($group, 404);

        $isOwner     = (int) $group->instructor_id === (int) $user->id;
        $isCoTrainer = $group->coFormateurs()->where('users.id', (int) $user->id)->exists();
        $isMember    = $group->users()->where('users.id', (int) $user->id)->exists();

        abort_unless($isOwner || $isCoTrainer || $isMember, 403);
    }
}
