<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ModuleLecture;
use App\Models\Progression;
use Illuminate\Support\Facades\Storage;

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
    // On récupère la leçon avec ses relations SCORM si nécessaire
    $lecture = \App\Models\ModuleLecture::with(['scormPackage.activeVersion', 'scormPackageVersion'])->findOrFail($id);

    // Correction : On utilise prioritairement scorm_path qui est la colonne alimentée par l'importateur
    // On retire la vérification de scorm_index_path si vous ne l'utilisez pas ailleurs.
    $path = $lecture->scorm_path;

    if (!$path) {
        // Au lieu d'un 404 brutal, on peut rediriger avec un message d'erreur
        return redirect()->back()->with('error', 'Le contenu SCORM n\'a pas encore été importé pour cette leçon.');
    }

    // On s'assure que le chemin commence correctement pour asset()
    // Si votre chemin en base est "modules/scorm/...", asset() générera "https://votresite.com/modules/scorm/..."
    return view('admin.backend.modules.lecture.lecture_scorm', [
        'lecture' => $lecture,
        'scormUrl' => asset($path),
    ]);
}

public function showSlides($id)
{
    $lecture = \App\Models\ModuleLecture::findOrFail($id);

    if (($lecture->content_type ?? 'scorm') !== 'slides') {
        return redirect()->back()->with('error', 'Cette leçon n\'est pas en mode Slides.');
    }

    if (($lecture->slides_status ?? null) !== 'ready' || empty($lecture->slides_path)) {
        return redirect()->back()->with('error', 'Les slides ne sont pas encore prêtes.');
    }

    $slides = collect(Storage::disk('public')->files($lecture->slides_path))
        ->filter(fn (string $file) => (bool) preg_match('/^slide[-_]\d+\.jpg$/i', basename($file)))
        ->sortBy(function (string $file): int {
            if (preg_match('/(\d+)\.jpg$/i', basename($file), $matches)) {
                return (int) $matches[1];
            }
            return PHP_INT_MAX;
        })
        ->values()
        ->map(fn (string $file) => route('media.storage', ['path' => $file], false))
        ->all();

    if (empty($slides)) {
        return redirect()->back()->with('error', 'Aucune slide n\'a été trouvée pour cette leçon.');
    }

    return view('admin.backend.modules.lecture.lecture_slides', [
        'lecture' => $lecture,
        'slides' => $slides,
    ]);
}


}
