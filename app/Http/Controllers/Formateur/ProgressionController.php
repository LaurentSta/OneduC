<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Progression;
use App\Models\User;
use App\Models\ModuleLecture;

class ProgressionController extends Controller
{
    public function index()
    {
        $formateurId = auth()->id();

        $progressions = \App\Models\Progression::with(['user', 'lecture.section.module'])
            ->whereHas('user', function ($query) use ($formateurId) {
                $query->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($q) use ($formateurId) {
                        $q->where('instructor_id', $formateurId);
                    });
            })
            ->orderByDesc('completed_at')
            ->get();

        return view('formateur.progressions.index', compact('progressions'));
    }
    public function markCompleted(Request $request)
    {


        $userId = auth()->id();
        $lectureId = $request->input('lecture_id');

        if (!$userId || !$lectureId) {
            return response()->json(['error' => 'Données manquantes'], 400);
        }

        \App\Models\Progression::firstOrCreate([
            'user_id' => $userId,
            'lecture_id' => $lectureId,
        ], [
            'completed_at' => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }

}
