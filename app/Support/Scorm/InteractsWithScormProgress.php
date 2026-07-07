<?php

namespace App\Support\Scorm;

use App\Models\ModuleLecture;

trait InteractsWithScormProgress
{
    /**
     * Calcule le temps cumule en gerant les deltas de session.
     */
    private function handleSessionTime($sc, string $scormValue): void
    {
        // On ignore les centiemes de secondes potentiels
        $cleanTime = explode('.', $scormValue)[0];
        $parts = explode(':', $cleanTime);

        if (count($parts) === 3) {
            [$h, $m, $s] = array_map('intval', $parts);
            $durationSeconds = ($h * 3600) + ($m * 60) + $s;

            $lastSessionTime = (int) $sc->last_session_time;
            $delta = $durationSeconds - $lastSessionTime;

            // On cumule uniquement si le temps est positif (nouvelle donnee)
            if ($delta > 0) {
                $sc->session_time = (int) $sc->session_time + $delta;
                $sc->last_session_time = $durationSeconds;
            } elseif ($durationSeconds < $lastSessionTime) {
                // Si le player a reset son compteur interne, on met juste a jour le repere
                $sc->last_session_time = $durationSeconds;
            }
        }
    }

    /**
     * Force la completion si les conditions metier sont remplies.
     */
    private function recomputeMonotoneStatus($sc, int $lectureId): void
    {
        if ($sc->is_completed) {
            return;
        }

        $lecture = ModuleLecture::find($lectureId);
        if (! $lecture) {
            return;
        }

        // Seuil de reussite par defaut a 50% ou selon vos besoins
        $passThreshold = 50;

        if ($sc->best_score >= $passThreshold) {
            $sc->lesson_status = 'completed';
            $sc->is_completed = true;
            $sc->save();
        }
    }
}
