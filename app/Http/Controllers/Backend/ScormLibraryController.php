<?php

namespace App\Http\Controllers\Backend;

use App\Domains\ModulesFormateur\Support\AccesFormationCatalogue;
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
    public function __construct(private readonly AccesFormationCatalogue $accesCatalogue) {}

    public function importForLecture(ScormImportRequest $request, ScormImporter $importer): RedirectResponse
    {
        $lecture = ModuleLecture::findOrFail($request->lecture_id);
        $this->accesCatalogue->assertEditable($lecture->module()->firstOrFail());
        $lecture->loadMissing(['scormPackage.activeVersion', 'scormPackageVersion']);
        $previousPath = $lecture->scorm_index_path;

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
                'content_type' => 'scorm',
                'scorm_path' => $result->relative_index_path,
                'scorm_package_id' => $result->package_id,
                'scorm_package_version_id' => $result->version_id,
            ]);

            return back()->with([
                'success_scorm_v2' => 'Contenu SCORM mis à jour avec succès.',
                'previous_scorm_path' => $previousPath,
                'new_scorm_path' => $result->relative_index_path,
                'scorm_imported_at' => $result->imported_at ?? null,
                'scorm_import_hash' => $result->zip_hash ?? null,
                'scorm_cache_token' => $result->cache_token ?? null,
            ]);
        } catch (Throwable $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }
}
