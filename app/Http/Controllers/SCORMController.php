<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\ScormResult;
use App\Models\ScormScore;
use App\Models\ScormInteraction;

class SCORMController extends Controller
{
    public function saveProgress(Request $request)
    {
        $userId     = Auth::id();
        $lectureId  = (int) $request->input('lecture_id');
        $scormKey   = (string) $request->input('scorm_key');
        $scormValue = $request->input('scorm_value');

        if (!$userId || !$lectureId || $scormKey === '') {
            Log::warning('SCORM contexte incomplet', compact('userId','lectureId','scormKey'));
            return response()->json(['error' => 'missing'], 400);
        }
        if (is_null($scormValue)) {
            Log::warning('SCORM valeur nulle', compact('userId','lectureId','scormKey'));
            return response()->json(['error' => 'null'], 400);
        }

        // 1) Trace brute
        ScormResult::updateOrCreate(
            ['user_id' => $userId, 'lecture_id' => $lectureId, 'scorm_key' => $scormKey],
            ['scorm_value' => (string) $scormValue]
        );

    

        // 3) Temps de session HH:MM:SS
        if ($scormKey === 'cmi.core.session_time') {
            $parts = explode(':', (string) $scormValue);
            if (count($parts) === 3) {
                [$h, $m, $s] = array_map('intval', $parts);
                $duration = ($h * 3600) + ($m * 60) + $s;

                $sc = ScormScore::firstOrNew(['user_id' => $userId, 'lecture_id' => $lectureId]);

                $last = (int) ($sc->last_session_time ?? 0);
                $delta = $duration - $last;

                // On ne cumule que si le temps avance (évite doublons/reset SCORM)
                if ($delta > 0 && $delta < 24 * 3600) {
                    $sc->session_time = (int) ($sc->session_time ?? 0) + $delta;
                    $sc->last_session_time = $duration;
                } else {
                    // si reset ou incohérence, on remet juste last_session_time
                    $sc->last_session_time = $duration;
                }

                $sc->last_attempt_at = now(); // utile comme "dernière activité"
                $sc->save();
            }
        }


        

        // 5) Upgrade-only si le SCO envoie "completed/passed"
        if ($scormKey === 'cmi.core.lesson_status') {
            $val = strtolower((string) $scormValue);
            if (in_array($val, ['completed','passed'], true)) {
                $sc = ScormScore::firstOrNew(['user_id' => $userId, 'lecture_id' => $lectureId]);
                if ($sc->lesson_status !== 'completed') {
                    $sc->lesson_status = 'completed';
                    $sc->is_completed  = true;
                    $sc->save();
                }
            }
        }

        return response()->json([
            'success' => true,
            'lesson_status' => $lessonStatus, // valeur interne Onéduc : completed / in_progress / failed...
            'scorm_lesson_status' => $scormLessonStatus, // valeur SCORM reçue : passed / completed / incomplete...
        ]);
    }

    private function recomputeMonotoneStatus(int $userId, int $lectureId): void
    {
        $sc = ScormScore::firstOrNew(['user_id' => $userId, 'lecture_id' => $lectureId]);

        $score = $sc->last_score ?? (int) ScormResult::where('user_id',$userId)
            ->where('lecture_id',$lectureId)
            ->where('scorm_key','cmi.core.score.raw')
            ->value('scorm_value');

        // décompte par ID de question distinct en base interactions
        $answered = (int) DB::table('scorm_interactions')
            ->where('user_id',$userId)
            ->where('lecture_id',$lectureId)
            ->selectRaw("COUNT(DISTINCT SUBSTRING_INDEX(interaction_id,'_',1)) as c")
            ->value('c');

        $sc->questions_answered = $answered;

        $lecture = \App\Models\ModuleLecture::find($lectureId);
        $expected = $lecture?->quiz_questions_per_attempt ?? 0;
        $eligible = ($expected === 0) || ($answered >= $expected);
        $passThreshold = 50;

        $prev = $sc->lesson_status;
        $next = $prev;

        if ($prev !== 'completed') {
            if ($eligible && $score >= $passThreshold) {
                $next = 'completed';
            } elseif ($eligible) {
                $next = 'failed';
            } else {
                $next = 'incomplete';
            }
        }

        $sc->lesson_status = $next;
        $sc->is_completed  = ($next === 'completed');
        $sc->save();
    }
}
