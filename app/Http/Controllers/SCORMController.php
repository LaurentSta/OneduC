<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ScormResult;
use App\Models\ScormScore;
use App\Models\ScormInteraction;

class SCORMController extends Controller
{
    public function saveProgress(Request $request)
    {
        $userId     = Auth::id();
        $lectureId  = $request->input('lecture_id');
        $scormKey   = $request->input('scorm_key');
        $scormValue = $request->input('scorm_value');

        if (!$userId || !$lectureId || !$scormKey) {
            Log::warning('⚠️ Contexte SCORM incomplet');
            return response()->json(['error' => 'Contenu manquant'], 400);
        }

        if (is_null($scormValue)) {
            Log::warning('⚠️ scorm_value est nul', compact('userId', 'lectureId', 'scormKey'));
            return response()->json(['error' => 'scorm_value est requis'], 400);
        }

        // 🎯 1. Résultat brut dans scorm_results
        ScormResult::updateOrCreate(
            ['user_id' => $userId, 'lecture_id' => $lectureId, 'scorm_key' => $scormKey],
            ['scorm_value' => $scormValue]
        );

        // 🎯 2. Score global résumé
        if ($scormKey === 'cmi.core.score.raw') {
            $score = intval($scormValue);

            $answered = ScormResult::where('user_id', $userId)
                ->where('lecture_id', $lectureId)
                ->where('scorm_key', 'like', 'cmi.interactions%.result')
                ->count();

            $scormScore = ScormScore::firstOrNew([
                'user_id' => $userId,
                'lecture_id' => $lectureId,
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

            // 🧠 Calcul statut
            $lecture = \App\Models\ModuleLecture::find($lectureId);
            $expectedQuestions = $lecture?->question_count ?? 0;

            $status = null;
            if ($expectedQuestions > 0) {
                if ($answered >= $expectedQuestions) {
                    $status = $score >= 50 ? 'completed' : 'failed';
                } else {
                    $status = 'incomplete';
                }
            }

            $scormScore->lesson_status = $status;
            $scormScore->save();

            Log::info('✅ SCORM reçu', compact('userId', 'lectureId', 'scormKey', 'scormValue', 'status'));
        }

        // 🕒 3. Temps de session
        if ($scormKey === 'cmi.core.session_time') {
            $parts = explode(':', $scormValue);
            if (count($parts) === 3) {
                [$h, $m, $s] = array_map('intval', $parts);
                $duration = ($h * 3600) + ($m * 60) + $s;

                $scormScore = ScormScore::firstOrNew([
                    'user_id' => $userId,
                    'lecture_id' => $lectureId,
                ]);

                $scormScore->session_time = max($scormScore->session_time ?? 0, $duration);
                $scormScore->save();
            }
        }

        // 🧠 4. Données d’interaction détaillées
        if (
            str_starts_with($scormKey, 'cmi.interactions') &&
            preg_match('/cmi\.interactions\.([^.]+)\.(.+)/', $scormKey, $matches)
        ) {
            $index = $matches[1];
            $field = str_replace(['.0.', '.0'], '_0_', $matches[2]); // normalisation

            $data = session()->get("interaction_{$index}", []);
            $data[$field] = $scormValue;
            session()->put("interaction_{$index}", $data);

            $required = ['id', 'type', 'result', 'student_response', 'correct_responses_0_pattern', 'weighting'];

            if (count(array_intersect($required, array_keys($data))) === count($required)) {
                $interactionId = $data['id'] . '_' . uniqid();

                ScormInteraction::create([
                    'user_id'               => $userId,
                    'lecture_id'            => $lectureId,
                    'interaction_id'        => $interactionId,
                    'interaction_key'       => md5($userId . '_' . $lectureId . '_' . $interactionId),
                    'interaction_type'      => $data['type'] ?? null,
                    'result'                => $data['result'] ?? null,
                    'response'              => $data['student_response'] ?? null,
                    'correct_response'      => $data['correct_responses_0_pattern'] ?? null,
                    'latency'               => $data['latency'] ?? null,
                    'time'                  => $data['time'] ?? now()->format('H:i:s'),
                    'interaction_weighting' => $data['weighting'] ?? null,
                ]);

                session()->forget("interaction_{$index}");
            }
        }

        return response()->json(['success' => true]);
    }
}
