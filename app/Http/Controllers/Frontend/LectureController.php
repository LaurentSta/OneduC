<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ModuleLecture;
use App\Models\Progression;

class LectureController extends Controller
{
    public function show($id)
    {
        $lecture = ModuleLecture::with(['section.module.sections.lectures'])->findOrFail($id);
        $module = $lecture->section->module;

        return view('frontend.contenu.lecture_show', compact('lecture', 'module'));
    }

    /**
     * Enregistre la progression de l'utilisateur pour une leçon donnée
     */
    public function valider($id, Request $request)
    {
        $user = auth()->user();

        \App\Models\Progression::updateOrCreate(
            [
                'user_id' => $user->id,
                'lecture_id' => $id,
            ],
            [
                'completed_at' => now(),
            ]
        );

        // Redirection automatique si spécifiée
        if ($request->has('redirect_to')) {
            return redirect($request->input('redirect_to'));
        }

        return redirect()->back()->with('success', 'Leçon validée !');
    }
    public function showScorm($id)
    {
        $lecture = \App\Models\ModuleLecture::findOrFail($id);

        if (!$lecture->scorm_path) {
            abort(404, 'Aucun fichier SCORM défini pour cette lecture.');
        }

        return view('lectures.lecture_scorm', compact('lecture'));
    }



}
