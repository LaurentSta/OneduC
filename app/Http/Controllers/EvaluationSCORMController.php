<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ScormEvaluationInteraction;
use App\Models\ScormEvaluationResult;
use App\Models\ScormEvaluationScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EvaluationSCORMController extends Controller
{
    public function saveEvaluationProgress(Request $request)
    {
        $userId = Auth::id();
        $evaluationId = $request->input('evaluation_id');
        $scormKey = $request->input('scorm_key');
        $scormValue = $request->input('scorm_value');

        if (! $userId || ! $evaluationId || ! $scormKey) {
            Log::warning('⚠️ Contexte SCORM incomplet (évaluation)');

            return response()->json(['error' => 'Contenu manquant'], 400);
        }

        if (is_null($scormValue)) {
            Log::warning('⚠️ scorm_value est nul (évaluation)', compact('userId', 'evaluationId', 'scormKey'));

            return response()->json(['error' => 'scorm_value est requis'], 400);
        }

        $moduleIds = Module::where('evaluation_id', $evaluationId)->pluck('id');
        $hasAccess = $moduleIds->contains(fn ($moduleId) => Auth::user()->aAccesAuModule($moduleId));
        if (! $hasAccess) {
            return response()->json(['error' => 'Accès non autorisé à cette évaluation'], 403);
        }

        // 🔁 1. Résultat brut
        ScormEvaluationResult::updateOrCreate(
            ['user_id' => $userId, 'evaluation_id' => $evaluationId, 'scorm_key' => $scormKey],
            ['scorm_value' => $scormValue]
        );

        // 🎯 2. Score global
        if ($scormKey === 'cmi.core.score.raw') {
            $score = intval($scormValue);
            $answered = ScormEvaluationResult::where('user_id', $userId)
                ->where('evaluation_id', $evaluationId)
                ->where('scorm_key', 'like', 'cmi.interactions%.result')
                ->count();

            $scormScore = ScormEvaluationScore::firstOrNew([
                'user_id' => $userId,
                'evaluation_id' => $evaluationId,
            ]);

            if (is_null($scormScore->first_score)) {
                $scormScore->first_score = $score;
                $scormScore->attempts_count = 1;
            } else {
                $scormScore->attempts_count = ($scormScore->attempts_count ?? 1) + 1;
            }

            $scormScore->last_score = $score;
            $scormScore->last_attempt_at = now();
            $scormScore->best_score = max($scormScore->best_score ?? 0, $score);
            $scormScore->is_completed = $scormScore->best_score >= 75;
            $scormScore->questions_answered = $answered;
            $scormScore->save();
        }

        // 🟢 3. lesson_status
        if ($scormKey === 'cmi.core.lesson_status') {
            $existing = ScormEvaluationScore::where('user_id', $userId)
                ->where('evaluation_id', $evaluationId)
                ->first();

            if (! $existing) {
                ScormEvaluationScore::create([
                    'user_id' => $userId,
                    'evaluation_id' => $evaluationId,
                    'lesson_status' => $scormValue,
                ]);
            } elseif ($existing->lesson_status !== 'completed') {
                $existing->lesson_status = $scormValue;
                $existing->save();
            }
        }

        // 🕒 4. session_time
        if ($scormKey === 'cmi.core.session_time') {
            $parts = explode(':', $scormValue);
            if (count($parts) === 3) {
                [$h, $m, $s] = array_map('intval', $parts);
                $duration = ($h * 3600) + ($m * 60) + $s;

                $scormScore = ScormEvaluationScore::firstOrNew([
                    'user_id' => $userId,
                    'evaluation_id' => $evaluationId,
                ]);

                $scormScore->session_time = ($scormScore->session_time ?? 0) + $duration;
                $scormScore->save();
            }
        }

        // 🧠 5. Interactions
        if (str_starts_with($scormKey, 'cmi.interactions') && preg_match('/cmi\.interactions\.([^.]+)\.(.+)/', $scormKey, $matches)) {
            $index = $matches[1];
            $field = str_replace(['.0.', '.0'], '_0_', $matches[2]);

            $data = session()->get("eval_interaction_{$index}", []);
            $data[$field] = $scormValue;
            session()->put("eval_interaction_{$index}", $data);

            $required = ['id', 'type', 'result', 'student_response', 'correct_responses_0_pattern', 'weighting'];
            if (count(array_intersect($required, array_keys($data))) === count($required)) {
                ScormEvaluationInteraction::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'evaluation_id' => $evaluationId,
                        'interaction_id' => $data['id'] ?? "interaction_$index",
                    ],
                    [
                        'interaction_type' => $data['type'] ?? null,
                        'result' => $data['result'] ?? null,
                        'response' => $data['student_response'] ?? null,
                        'correct_response' => $data['correct_responses_0_pattern'] ?? null,
                        'latency' => $data['latency'] ?? null,
                        'time' => $data['time'] ?? null,
                        'interaction_weighting' => $data['weighting'] ?? null,
                    ]
                );
                session()->forget("eval_interaction_{$index}");
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function fin(Evaluation $evaluation)
    {
        $userId = auth()->id();

        $score = ScormEvaluationScore::where('user_id', $userId)
            ->where('evaluation_id', $evaluation->id)
            ->first();

        $answered = ScormEvaluationInteraction::where('user_id', $userId)
            ->where('evaluation_id', $evaluation->id)
            ->count();

        $base = $this->viewBase(); // 'formateur.formations' | 'stagiaire.formations'

        return view("$base.evaluations.fin_evaluation", [
            'evaluation' => $evaluation,
            'lastScore' => $score?->last_score,
            'bestScore' => $score?->best_score,
            'attempts' => $score?->attempts_count ?? 1,
            'sessionTimeSeconds' => $score?->session_time ?? 0,
            'questionsAnswered' => $answered,
        ]);
    }
}
