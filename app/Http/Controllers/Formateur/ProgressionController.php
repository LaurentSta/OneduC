<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Progression;
use App\Services\LearningAnalyticsService;
use Illuminate\Http\Request;

class ProgressionController extends Controller
{
    public function __construct(
        private readonly LearningAnalyticsService $learningAnalytics,
    ) {
    }

    public function markCompleted(Request $request)
    {
        $userId    = auth()->id();
        $lectureId = (int) $request->input('lecture_id');

        if (!$userId || !$lectureId) {
            return response()->json(['error' => 'Données manquantes'], 400);
        }

        Progression::firstOrCreate(
            [
                'user_id'    => $userId,
                'lecture_id' => $lectureId,
            ],
            [
                'completed_at' => now(),
            ]
        );

        return response()->json(['status' => 'ok']);
    }
}
