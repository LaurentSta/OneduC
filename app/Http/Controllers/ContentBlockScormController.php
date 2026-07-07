<?php

namespace App\Http\Controllers;

use App\Models\ContentBlockScormResult;
use App\Models\ContentBlockScormScore;
use App\Models\ModuleLecture;
use App\Support\Scorm\InteractsWithScormProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContentBlockScormController extends Controller
{
    use InteractsWithScormProgress;

    /**
     * Sauvegarde la progression d'un bloc SCORM embarqué dans une leçon mixte.
     * Miroir de SCORMController::saveProgress, mais scopé par content_block_key
     * pour permettre plusieurs paquets SCORM indépendants dans une même leçon.
     */
    public function saveProgress(Request $request)
    {
        $userId = Auth::id();
        $lectureId = (int) $request->input('lecture_id');
        $blockKey = (string) $request->input('content_block_key');
        $scormKey = (string) $request->input('scorm_key');
        $scormValue = $request->input('scorm_value');

        if (! $userId || ! $lectureId || $blockKey === '' || $scormKey === '') {
            return response()->json(['error' => 'Incomplet'], 400);
        }

        $lecture = ModuleLecture::find($lectureId);
        if (! $lecture || ! Auth::user()->aAccesAuModule($lecture->module_id)) {
            return response()->json(['error' => 'Accès non autorisé à cette leçon'], 403);
        }

        ContentBlockScormResult::updateOrCreate(
            ['user_id' => $userId, 'lecture_id' => $lectureId, 'content_block_key' => $blockKey, 'scorm_key' => $scormKey],
            ['scorm_value' => (string) $scormValue]
        );

        $sc = ContentBlockScormScore::firstOrCreate(
            ['user_id' => $userId, 'lecture_id' => $lectureId, 'content_block_key' => $blockKey],
            ['lesson_status' => 'not_started', 'attempts_count' => 1]
        );

        switch ($scormKey) {
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

            case 'cmi.core.lesson_status':
            case 'cmi.completion_status':
            case 'cmi.success_status':
                $status = strtolower((string) $scormValue);
                if (! in_array($sc->lesson_status, ['completed', 'passed'])) {
                    $sc->lesson_status = $status;
                    if (in_array($status, ['completed', 'passed'])) {
                        $sc->is_completed = true;
                    }
                }
                break;

            case 'cmi.core.session_time':
                $this->handleSessionTime($sc, (string) $scormValue);
                break;
        }

        $sc->last_attempt_at = now();
        $sc->save();

        $this->recomputeMonotoneStatus($sc, $lectureId);

        return response()->json([
            'success' => true,
            'lesson_status' => $sc->is_completed ? 'completed' : 'in_progress',
            'scorm_lesson_status' => $sc->lesson_status,
        ]);
    }
}
