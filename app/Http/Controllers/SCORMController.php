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

        // 2) Score global
        if ($scormKey === 'cmi.core.score.raw') {
            $score = (int) $scormValue;

            $sc = ScormScore::firstOrNew(['user_id' => $userId, 'lecture_id' => $lectureId]);
            if (is_null($sc->first_score)) {
                $sc->first_score    = $score;
                $sc->attempts_count = 1;
            } else {
                $sc->attempts_count = ($sc->attempts_count ?? 1) + 1;
            }
            $sc->last_score      = $score;
            $sc->last_attempt_at = now();
            $sc->best_score      = max($sc->best_score ?? 0, $score);
            $sc->save();

            $this->recomputeMonotoneStatus($userId, $lectureId);
        }

        // 3) Temps de session HH:MM:SS
        if ($scormKey === 'cmi.core.session_time') {
            $parts = explode(':', (string) $scormValue);
            if (count($parts) === 3) {
                [$h, $m, $s] = array_map('intval', $parts);
                $duration = ($h * 3600) + ($m * 60) + $s;

                $sc = ScormScore::firstOrNew(['user_id' => $userId, 'lecture_id' => $lectureId]);
                $sc->session_time = max($sc->session_time ?? 0, $duration);
                $sc->save();
            }
        }

        // 4) Interactions SCORM 1.2 (support iSpring "NaN", multi-tentatives)
        if (
            str_starts_with($scormKey, 'cmi.interactions') &&
            preg_match('/^cmi\.interactions\.([^.]+)\.(.+)$/', $scormKey, $m)
        ) {
            $idx   = (string) $m[1]; // "0","1","NaN", etc.
            $field = (string) $m[2]; // id|type|student_response|learner_response|result|correct_responses.0.pattern|time|latency|weighting
            if (str_starts_with($field, 'correct_responses')) $field = 'correct_pattern';

            $isNaN = ($idx === 'NaN');

            if ($isNaN) {
                // sacs par ID de question
                $pendingKey   = "scorm_nan_pending_{$userId}_{$lectureId}";
                $currentIdKey = "scorm_nan_current_id_{$userId}_{$lectureId}";
                $byIdKey = fn(string $qid) => "scorm_byid_{$userId}_{$lectureId}_" . md5($qid);

                if ($field === 'id') {
                    $qid = (string) $scormValue;
                    cache()->put($currentIdKey, $qid, 300);

                    $bagKey = $byIdKey($qid);
                    $bag = cache()->get($bagKey, [
                        'user_id' => $userId, 'lecture_id' => $lectureId,
                        'interaction_idx' => $idx, 'id' => $qid,
                        'uid' => Str::uuid()->toString(), 'row_id' => null,
                    ]);
                    $pend = cache()->pull($pendingKey, []);
                    if ($pend) $bag = array_merge($bag, $pend);
                    cache()->put($bagKey, $bag, 300);
                } else {
                    $qid = cache()->get($currentIdKey);
                    if ($qid) {
                        $bagKey = $byIdKey($qid);
                        $bag = cache()->get($bagKey, [
                            'user_id' => $userId, 'lecture_id' => $lectureId,
                            'interaction_idx' => $idx, 'id' => $qid,
                            'uid' => Str::uuid()->toString(), 'row_id' => null,
                        ]);
                    } else {
                        $bagKey = $pendingKey;
                        $bag = cache()->get($bagKey, [
                            'user_id' => $userId, 'lecture_id' => $lectureId,
                            'interaction_idx' => $idx,
                            'uid' => Str::uuid()->toString(), 'row_id' => null,
                        ]);
                    }

                    switch ($field) {
                        case 'type':              $bag['type'] = (string) $scormValue; break;
                        case 'student_response':
                        case 'learner_response':  $bag['response'] = (string) $scormValue; break;
                        case 'result':            $bag['result'] = (string) $scormValue; break;
                        case 'latency':           $bag['latency'] = (string) $scormValue; break;
                        case 'time':              $bag['time'] = (string) $scormValue; break;
                        case 'weighting':         $bag['weighting'] = (string) $scormValue; break;
                        case 'correct_pattern':   $bag['correct_pattern'] = (string) $scormValue; break;
                        default: break;
                    }
                    cache()->put($bagKey, $bag, 300);

                    // nouvelle tentative: nouveau RESULT -> forcer nouvelle ligne
                    if ($field === 'result' && !empty($bag['row_id'])) {
                        $bag['uid'] = Str::uuid()->toString();
                        $bag['row_id'] = null;
                        cache()->put($bagKey, $bag, 300);
                    }
                }

                // insertion/complément
                $qid = cache()->get($currentIdKey);
                if ($qid) {
                    $bagKey = $byIdKey($qid);
                    $bag = cache()->get($bagKey);
                    $hasResult = !empty($bag['result']);
                    $hasId     = !empty($bag['id']);

                    if ($bag && $hasResult && $hasId && empty($bag['row_id'])) {
                        $interactionId = $bag['id'] . '_' . Str::uuid()->toString();
                        $timeVal = $bag['time'] ?? now()->format('H:i:s');

                        $row = ScormInteraction::create([
                            'user_id'               => $bag['user_id'],
                            'lecture_id'            => $bag['lecture_id'],
                            'interaction_id'        => $interactionId,
                            'interaction_type'      => $bag['type'] ?? null,
                            'interaction_weighting' => $bag['weighting'] ?? null,
                            'result'                => $bag['result'] ?? null,
                            'response'              => $bag['response'] ?? null,
                            'correct_response'      => $bag['correct_pattern'] ?? null,
                            'latency'               => $bag['latency'] ?? null,
                            'time'                  => $timeVal,
                        ]);

                        $bag['row_id'] = $row->id;
                        cache()->put($bagKey, $bag, 300);

                        $this->recomputeMonotoneStatus($userId, $lectureId);
                    } elseif ($bag && !empty($bag['row_id'])) {
                        $row = ScormInteraction::find($bag['row_id']);
                        if ($row) {
                            $upd = [];
                            if (isset($bag['correct_pattern']) && is_null($row->correct_response))    $upd['correct_response']      = $bag['correct_pattern'];
                            if (isset($bag['response'])        && is_null($row->response))             $upd['response']               = $bag['response'];
                            if (isset($bag['latency'])         && is_null($row->latency))              $upd['latency']                = $bag['latency'];
                            if (isset($bag['time'])            && is_null($row->time))                 $upd['time']                   = $bag['time'];
                            if (isset($bag['weighting'])       && is_null($row->interaction_weighting))$upd['interaction_weighting']  = $bag['weighting'];
                            if (isset($bag['type'])            && is_null($row->interaction_type))     $upd['interaction_type']       = $bag['type'];
                            if ($upd) $row->update($upd);
                        }
                    }
                }
            } else {
                // index normal
                $cacheKey = "scorm_interaction_{$userId}_{$lectureId}_{$idx}";
                $bag = cache()->get($cacheKey, [
                    'user_id' => $userId, 'lecture_id' => $lectureId,
                    'interaction_idx' => $idx, 'uid' => Str::uuid()->toString(), 'row_id' => null,
                ]);

                switch ($field) {
                    case 'id':                $bag['id'] = (string) $scormValue; break;
                    case 'type':              $bag['type'] = (string) $scormValue; break;
                    case 'student_response':
                    case 'learner_response':  $bag['response'] = (string) $scormValue; break;
                    case 'result':            $bag['result'] = (string) $scormValue; break;
                    case 'latency':           $bag['latency'] = (string) $scormValue; break;
                    case 'time':              $bag['time'] = (string) $scormValue; break;
                    case 'weighting':         $bag['weighting'] = (string) $scormValue; break;
                    case 'correct_pattern':   $bag['correct_pattern'] = (string) $scormValue; break;
                    default: break;
                }
                cache()->put($cacheKey, $bag, 300);

                if ($field === 'result' && !empty($bag['row_id'])) {
                    $bag['uid'] = Str::uuid()->toString();
                    $bag['row_id'] = null;
                    cache()->put($cacheKey, $bag, 300);
                }

                $hasResult = !empty($bag['result']);
                $hasId     = !empty($bag['id']);

                if ($hasResult && $hasId && empty($bag['row_id'])) {
                    $interactionId = $bag['id'] . '_' . Str::uuid()->toString();
                    $timeVal = $bag['time'] ?? now()->format('H:i:s');

                    $row = ScormInteraction::create([
                        'user_id'               => $bag['user_id'],
                        'lecture_id'            => $bag['lecture_id'],
                        'interaction_id'        => $interactionId,
                        'interaction_type'      => $bag['type'] ?? null,
                        'interaction_weighting' => $bag['weighting'] ?? null,
                        'result'                => $bag['result'] ?? null,
                        'response'              => $bag['response'] ?? null,
                        'correct_response'      => $bag['correct_pattern'] ?? null,
                        'latency'               => $bag['latency'] ?? null,
                        'time'                  => $timeVal,
                    ]);

                    $bag['row_id'] = $row->id;
                    cache()->put($cacheKey, $bag, 300);

                    $this->recomputeMonotoneStatus($userId, $lectureId);
                } elseif (!empty($bag['row_id'])) {
                    $row = ScormInteraction::find($bag['row_id']);
                    if ($row) {
                        $upd = [];
                        if (isset($bag['correct_pattern']) && is_null($row->correct_response))    $upd['correct_response']      = $bag['correct_pattern'];
                        if (isset($bag['response'])        && is_null($row->response))             $upd['response']               = $bag['response'];
                        if (isset($bag['latency'])         && is_null($row->latency))              $upd['latency']                = $bag['latency'];
                        if (isset($bag['time'])            && is_null($row->time))                 $upd['time']                   = $bag['time'];
                        if (isset($bag['weighting'])       && is_null($row->interaction_weighting))$upd['interaction_weighting']  = $bag['weighting'];
                        if (isset($bag['type'])            && is_null($row->interaction_type))     $upd['interaction_type']       = $bag['type'];
                        if ($upd) $row->update($upd);
                    }
                }
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

        return response()->json(['success' => true]);
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
