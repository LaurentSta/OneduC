<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Scorm\ScormImportRequest;
use App\Models\ModuleLecture;
use App\Services\Scorm\ScormImporter;
use App\Support\LearningAssetPath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Throwable;

class ScormLibraryController extends Controller
{
    public function importForLecture(ScormImportRequest $request, ScormImporter $importer): RedirectResponse
    {
        $lecture = ModuleLecture::findOrFail($request->lecture_id);

        try {
            $targetFolder = LearningAssetPath::lessonImportFolder((int) $lecture->id);
            $fullPath = public_path($targetFolder);

            if (File::exists($fullPath)) {
                File::cleanDirectory($fullPath);
            }

            $result = $importer->importToFolder(
                zipFile: $request->file('zip'),
                targetPath: $targetFolder
            );

            $lecture->update([
                'scorm_path' => $result->relative_index_path,
                'scorm_package_id' => $result->package_id,
                'scorm_package_version_id' => $result->version_id,
            ]);

            return back()->with([
                'success_scorm_v2' => 'Contenu SCORM mis à jour avec succès.',
                'new_scorm_path' => $result->relative_index_path,
            ]);
        } catch (Throwable $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }
}
