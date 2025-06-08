<?php

namespace App\Http\Controllers;

use App\Models\VideoSegmentTracking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VideoSegmentTrackingController extends Controller
{
    public function store(Request $request)
{
    $data = $request->validate([
        'lecture_id' => 'required|exists:module_lectures,id',
        'segment_start' => 'required|integer',
        'segment_end' => 'required|integer',
        'watch_time' => 'required|numeric'
    ]);

    $tracking = VideoSegmentTracking::firstOrNew([
        'user_id' => auth()->id(),
        'lecture_id' => $data['lecture_id'],
        'segment_start' => $data['segment_start'],
        'segment_end' => $data['segment_end'],
    ]);

    if (!$tracking->exists) {
        $tracking->watch_count = 0;
        $tracking->total_watch_time = 0;
        $tracking->lecture_id = $data['lecture_id']; // ← Redondant mais essentiel pour éviter une erreur SQL
    }

    $tracking->watch_count += 1;
    $tracking->total_watch_time += $data['watch_time'];
    $tracking->save();

    return response()->json(['status' => 'ok']);
}

}
