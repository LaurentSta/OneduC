<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Models\Module;
use App\Models\ScormPackageVersion;
use App\Services\Scorm\ScormImporter;
use App\Support\LearningAssetPath;
use Illuminate\Http\UploadedFile;

class TeleverserScormModule
{
    public function __construct(private readonly ScormImporter $scormImporter) {}

    public function execute(Module $module, UploadedFile $zipFile, string $contentBlockKey): ScormPackageVersion
    {
        $targetPath = LearningAssetPath::lessonBlockScormFolder($module->id, $contentBlockKey);

        $result = $this->scormImporter->importToFolder($zipFile, $targetPath);

        return ScormPackageVersion::findOrFail($result->version_id);
    }
}
