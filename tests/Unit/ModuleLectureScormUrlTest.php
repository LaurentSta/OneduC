<?php

use App\Models\ModuleLecture;
use App\Models\ScormPackage;
use App\Models\ScormPackageVersion;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class);

it('uses active scorm version path and appends imported_at cache token', function () {
    $path = 'modules/00_Lecons/lecture_42/res/index.html';
    $importedAt = Carbon::parse('2026-03-08 12:34:56');

    $activeVersion = new ScormPackageVersion([
        'index_path' => $path,
        'imported_at' => $importedAt,
    ]);

    $package = new ScormPackage();
    $package->setRelation('activeVersion', $activeVersion);

    $lecture = new ModuleLecture([
        'use_active_scorm_version' => true,
        'scorm_path' => 'modules/legacy/index.html',
    ]);
    $lecture->setRelation('scormPackage', $package);

    expect($lecture->scorm_index_path)->toBe($path);
    expect($lecture->scorm_asset_url)->toBe(asset($path) . '?v=' . $importedAt->timestamp);
});

it('falls back to scorm_path when no version relation exists', function () {
    $path = 'modules/00_Lecons/lecture_99/index_lms.html';
    $updatedAt = Carbon::parse('2026-03-08 09:15:00');

    $lecture = new ModuleLecture([
        'use_active_scorm_version' => true,
        'scorm_path' => $path,
    ]);
    $lecture->updated_at = $updatedAt;

    expect($lecture->scorm_index_path)->toBe($path);
    expect($lecture->scorm_asset_url)->toBe(asset($path) . '?v=' . $updatedAt->timestamp);
});
