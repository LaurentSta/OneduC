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

        // 🔁 1. Enregistrement brut dans scorm_results
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

            $scormScore->save();
        }

        // ❗ 3. lesson_status = completed / incomplete
        if ($scormKey === 'cmi.core.lesson_status') {
            $existing = ScormScore::where('user_id', $userId)
                ->where('lecture_id', $lectureId)
                ->first();

            // Si aucune entrée → on crée
            if (!$existing) {
                ScormScore::create([
                    'user_id' => $userId,
                    'lecture_id' => $lectureId,
                    'lesson_status' => $scormValue,
                ]);
            } else {
                // Si déjà 'completed', on ne modifie pas
                if ($existing->lesson_status !== 'completed') {
                    $existing->lesson_status = $scormValue;
                    $existing->save();
                }
            }
        }


        // 🕒 Traitement de cmi.core.session_time
        if ($scormKey === 'cmi.core.session_time') {
            // Convertir "HH:MM:SS" → secondes
            $parts = explode(':', $scormValue);
            if (count($parts) === 3) {
                [$h, $m, $s] = array_map('intval', $parts);
                $duration = ($h * 3600) + ($m * 60) + $s;

                $scormScore = ScormScore::firstOrNew([
                    'user_id' => $userId,
                    'lecture_id' => $lectureId,
                ]);

                $scormScore->session_time = ($scormScore->session_time ?? 0) + $duration;
                $scormScore->save();
            }
        }


        // 🧠 4. Données d'interaction détaillées
        if (str_starts_with($scormKey, 'cmi.interactions') && preg_match('/cmi\.interactions\.([^.]+)\.(.+)/', $scormKey, $matches)) {
            $index = $matches[1];
            $field = str_replace(['.0.', '.0'], '_0_', $matches[2]); // 🔁 Normalisation

            $data = session()->get("interaction_{$index}", []);
            $data[$field] = $scormValue;
            session()->put("interaction_{$index}", $data);

            $required = ['id', 'type', 'result', 'student_response', 'correct_responses_0_pattern', 'weighting'];
            if (count(array_intersect($required, array_keys($data))) === count($required)) {
                ScormInteraction::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'lecture_id' => $lectureId,
                        'interaction_id' => $data['id'] ?? "interaction_$index"
                    ],
                    [
                        'interaction_type'        => $data['type'] ?? null,
                        'result'                  => $data['result'] ?? null,
                        'response'                => $data['student_response'] ?? null,
                        'correct_response'        => $data['correct_responses_0_pattern'] ?? null,
                        'latency'                 => $data['latency'] ?? null,
                        'time'                    => $data['time'] ?? null,
                        'interaction_weighting'   => $data['weighting'] ?? null,
                    ]
                );
                session()->forget("interaction_{$index}");
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
