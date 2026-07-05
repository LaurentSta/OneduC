<?php

namespace App\Http\Controllers;

use App\Models\ModuleLecture;
use App\Models\ScormResult;
use App\Models\ScormScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SCORMController extends Controller
{
    /**
     * Sauvegarde la progression envoyée par l'API SCORM 1.2 (JS).
     */
    public function saveProgress(Request $request)
    {
        $userId = Auth::id();
        $lectureId = (int) $request->input('lecture_id');
        $scormKey = (string) $request->input('scorm_key');
        $scormValue = $request->input('scorm_value');

        // Validation de base
        if (! $userId || ! $lectureId || $scormKey === '') {
            return response()->json(['error' => 'Incomplet'], 400);
        }

        $lecture = ModuleLecture::find($lectureId);
        if (! $lecture || ! Auth::user()->aAccesAuModule($lecture->module_id)) {
            return response()->json(['error' => 'Accès non autorisé à cette leçon'], 403);
        }

        // 1) Enregistrement de la trace brute (audit log)
        ScormResult::updateOrCreate(
            ['user_id' => $userId, 'lecture_id' => $lectureId, 'scorm_key' => $scormKey],
            ['scorm_value' => (string) $scormValue]
        );

        // 2) Initialisation de l'état consolidé
        $sc = ScormScore::firstOrCreate(
            ['user_id' => $userId, 'lecture_id' => $lectureId],
            ['lesson_status' => 'not_started', 'attempts_count' => 1]
        );

        // 3) Traitement logique par clé SCORM
        switch ($scormKey) {

            // Gestion du Score
            case 'cmi.core.score.raw':
                $score = (int) $scormValue;
                $sc->last_score = $score;
                if (is_null($sc->first_score)) {
                    $sc->first_score = $score;
                }
                if ($score > ($sc->best_score ?? -1)) {
                    $sc->best_score = $score;
                }
                break;

                // Gestion du Statut (SCORM 1.2 + 2004)
            case 'cmi.core.lesson_status':
            case 'cmi.completion_status':
            case 'cmi.success_status':
                $status = strtolower((string) $scormValue);
                // On ne rétrograde jamais un statut "completed"
                if (! in_array($sc->lesson_status, ['completed', 'passed'])) {
                    $sc->lesson_status = $status;
                    if (in_array($status, ['completed', 'passed'])) {
                        $sc->is_completed = true;
                    }
                }
                break;

                // Gestion du Temps (Format HH:MM:SS ou HH:MM:SS.s)
            case 'cmi.core.session_time':
                $this->handleSessionTime($sc, (string) $scormValue);
                break;
        }

        $sc->last_attempt_at = now();
        $sc->save();

        // 4) Recalcul optionnel de la complétion interne (ex: si score >= 50)
        $this->recomputeMonotoneStatus($sc);

        return response()->json([
            'success' => true,
            'lesson_status' => $sc->is_completed ? 'completed' : 'in_progress', // Statut interne Oneduc
            'scorm_lesson_status' => $sc->lesson_status, // Statut SCORM d'origine (pour API.js)
        ]);
    }

    /**
     * Calcule le temps cumulé en gérant les deltas de session.
     */
    private function handleSessionTime(ScormScore $sc, string $scormValue)
    {
        // On ignore les centièmes de secondes potentiels
        $cleanTime = explode('.', $scormValue)[0];
        $parts = explode(':', $cleanTime);

        if (count($parts) === 3) {
            [$h, $m, $s] = array_map('intval', $parts);
            $durationSeconds = ($h * 3600) + ($m * 60) + $s;

            $lastSessionTime = (int) $sc->last_session_time;
            $delta = $durationSeconds - $lastSessionTime;

            // On cumule uniquement si le temps est positif (nouvelle donnée)
            if ($delta > 0) {
                $sc->session_time = (int) $sc->session_time + $delta;
                $sc->last_session_time = $durationSeconds;
            } elseif ($durationSeconds < $lastSessionTime) {
                // Si le player a reset son compteur interne, on met juste à jour le repère
                $sc->last_session_time = $durationSeconds;
            }
        }
    }

    /**
     * Force la complétion si les conditions métier sont remplies.
     */
    private function recomputeMonotoneStatus(ScormScore $sc): void
    {
        if ($sc->is_completed) {
            return;
        }

        $lecture = ModuleLecture::find($sc->lecture_id);
        if (! $lecture) {
            return;
        }

        // Seuil de réussite par défaut à 50% ou selon vos besoins
        $passThreshold = 50;

        if ($sc->best_score >= $passThreshold) {
            $sc->lesson_status = 'completed';
            $sc->is_completed = true;
            $sc->save();
        }
    }
}
