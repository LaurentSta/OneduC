<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Scorm\ScormImportRequest;
use App\Models\ModuleLecture;
use App\Services\Scorm\ScormImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

class ScormLibraryController extends Controller
{
    // Dans ScormLibraryController.php
public function importForLecture(ScormImportRequest $request, ScormImporter $importer): RedirectResponse
{
    $lecture = ModuleLecture::findOrFail($request->lecture_id);
    
    try {
        $slug = \Illuminate\Support\Str::slug($lecture->lecture_title, '_');
        $targetFolder = "modules/scorm/00_Lecons/lecture_{$lecture->id}";
        $fullPath = public_path($targetFolder);

        if (\Illuminate\Support\Facades\File::exists($fullPath)) {
            \Illuminate\Support\Facades\File::cleanDirectory($fullPath);
        }

        // On n'envoie plus de paramètre inject_api ici
        $result = $importer->importToFolder(
            zipFile: $request->file('zip'),
            targetPath: $targetFolder
        );

        // ScormLibraryController.php

$lecture->update([
    'scorm_path' => $result->relative_index_path, // ex: modules/scorm/.../index_lms.html
    'scorm_package_id' => $result->package_id,
    'scorm_package_version_id' => $result->version_id,
]);

return back()->with([
    'success_scorm_v2' => "Contenu SCORM mis à jour avec succès.",
    'new_scorm_path' => $result->relative_index_path 
]);

    } catch (Throwable $e) {
        return back()->with('error', 'Erreur : ' . $e->getMessage());
    }
}
}