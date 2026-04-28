<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\ScaleSession;
use App\Models\ScaleSessionResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OutilsEchelleController extends Controller
{
    public function index(): View
    {
        $formateurId = (int) auth()->id();

        $groups = Group::query()
            ->accessibleByTrainer($formateurId)
            ->withCount('students')
            ->orderBy('name')
            ->get(['id', 'name']);

        $sessions = ScaleSession::query()
            ->where('formateur_id', $formateurId)
            ->with('group')
            ->withCount('responses')
            ->latest()
            ->limit(20)
            ->get();

        return view('formateur.outils.echelle_index', compact('groups', 'sessions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $formateurId = (int) auth()->id();

        $data = $request->validate([
            'group_id'                  => ['required', 'exists:groups,id'],
            'title'                     => ['nullable', 'string', 'max:255'],
            'questions'                 => ['required', 'array', 'min:1', 'max:10'],
            'questions.*.question'      => ['required', 'string', 'max:500'],
            'questions.*.label_min'     => ['nullable', 'string', 'max:100'],
            'questions.*.label_max'     => ['nullable', 'string', 'max:100'],
            'questions.*.min'           => ['required', 'integer', 'min:1', 'max:9'],
            'questions.*.max'           => ['required', 'integer', 'min:2', 'max:10'],
        ]);

        $group = Group::query()
            ->accessibleByTrainer($formateurId)
            ->findOrFail((int) $data['group_id']);

        $questions = collect($data['questions'])
            ->map(function (array $q): array {
                $min = (int) $q['min'];
                $max = (int) $q['max'];
                return [
                    'question'  => trim((string) $q['question']),
                    'label_min' => trim((string) ($q['label_min'] ?? '')),
                    'label_max' => trim((string) ($q['label_max'] ?? '')),
                    'min'       => min($min, $max - 1),
                    'max'       => max($max, $min + 1),
                ];
            })
            ->filter(fn (array $q): bool => $q['question'] !== '')
            ->values();

        abort_if($questions->isEmpty(), 422, 'L\'échelle doit contenir au moins une question valide.');

        $session = ScaleSession::query()->create([
            'formateur_id' => $formateurId,
            'group_id'     => $group->id,
            'title'        => trim((string) ($data['title'] ?? '')) ?: 'Échelle de positionnement',
            'questions'    => $questions->all(),
            'access_code'  => $this->generateCode(),
            'is_active'    => true,
            'opened_at'    => now(),
            'closed_at'    => null,
        ]);

        return redirect()->route('formateur.echelle.show', $session);
    }

    public function show(ScaleSession $scaleSession): View
    {
        $this->assertOwnership($scaleSession);
        $scaleSession->load('group');

        $stats = $this->buildStatsPayload($scaleSession);

        return view('formateur.outils.echelle_show', [
            'scaleSession' => $scaleSession,
            'joinUrl'      => route('echelle.join.code', $scaleSession->access_code),
            'stats'        => $stats,
        ]);
    }

    public function toggle(ScaleSession $scaleSession): RedirectResponse
    {
        $this->assertOwnership($scaleSession);

        $newStatus = ! $scaleSession->is_active;

        $scaleSession->update([
            'is_active'  => $newStatus,
            'opened_at'  => $newStatus ? now() : $scaleSession->opened_at,
            'closed_at'  => $newStatus ? null : now(),
        ]);

        return back()->with(
            'success',
            $newStatus ? 'L\'échelle est maintenant ouverte.' : 'L\'échelle est maintenant fermée.'
        );
    }

    public function state(ScaleSession $scaleSession): JsonResponse
    {
        $this->assertOwnership($scaleSession);

        return response()->json($this->buildStatsPayload($scaleSession));
    }

    private function buildStatsPayload(ScaleSession $scaleSession): array
    {
        $questions = collect($scaleSession->questions ?? [])->values();

        $totalRespondents = ScaleSessionResponse::query()
            ->where('scale_session_id', $scaleSession->id)
            ->distinct('user_id')
            ->count('user_id');

        $questionsPayload = $questions->map(function ($question, $qi) use ($scaleSession) {
            $min = (int) ($question['min'] ?? 1);
            $max = (int) ($question['max'] ?? 10);

            $values = ScaleSessionResponse::query()
                ->where('scale_session_id', $scaleSession->id)
                ->where('question_index', $qi)
                ->pluck('value')
                ->map(fn ($v) => (int) $v);

            $respondents = $values->count();
            $average     = $respondents > 0 ? round($values->avg(), 1) : null;

            $sorted  = $values->sort()->values();
            $median  = null;
            if ($respondents > 0) {
                $mid    = (int) floor(($respondents - 1) / 2);
                $median = $respondents % 2 === 1
                    ? $sorted[$mid]
                    : round(($sorted[$mid] + $sorted[$mid + 1]) / 2, 1);
            }

            $countsByValue = $values->countBy()->all();

            $distribution = collect(range($min, $max))->map(function (int $v) use ($countsByValue, $respondents): array {
                $count   = (int) ($countsByValue[$v] ?? 0);
                $percent = $respondents > 0 ? (int) round(($count / $respondents) * 100) : 0;
                return compact('v', 'count', 'percent') + ['value' => $v];
            })->values()->all();

            return [
                'question'    => (string) ($question['question'] ?? ''),
                'label_min'   => (string) ($question['label_min'] ?? ''),
                'label_max'   => (string) ($question['label_max'] ?? ''),
                'min'         => $min,
                'max'         => $max,
                'respondents' => $respondents,
                'average'     => $average,
                'median'      => $median,
                'distribution' => $distribution,
            ];
        })->values()->all();

        return [
            'is_active'        => (bool) $scaleSession->is_active,
            'respondents_total' => (int) $totalRespondents,
            'questions'        => $questionsPayload,
            'updated_at'       => now()->toIso8601String(),
        ];
    }

    private function assertOwnership(ScaleSession $scaleSession): void
    {
        abort_unless((int) $scaleSession->formateur_id === (int) auth()->id(), 403);
    }

    private function generateCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (ScaleSession::query()->where('access_code', Str::upper($code))->exists());

        return Str::upper($code);
    }
}
