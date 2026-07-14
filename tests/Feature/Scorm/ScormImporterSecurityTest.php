<?php

use App\Services\Scorm\ScormImporter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

function makeScormImporterSecurityUpload(array $files): UploadedFile
{
    $tmp = tempnam(sys_get_temp_dir(), 'oneduc_scorm_security_');
    $zipPath = $tmp.'.zip';
    rename($tmp, $zipPath);

    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    foreach ($files as $name => $contents) {
        $zip->addFromString($name, $contents);
    }

    $zip->close();

    return new UploadedFile($zipPath, 'scorm.zip', 'application/zip', null, true);
}

it('does not let a manifest href escape the extracted scorm release folder', function () {
    $targetPath = 'modules/test_scorm_security/'.Str::uuid();
    $escapeProbe = public_path($targetPath.'/escape-probe.html');

    File::ensureDirectoryExists(dirname($escapeProbe));
    File::put($escapeProbe, '<html><head></head><body>outside release</body></html>');

    $upload = makeScormImporterSecurityUpload([
        'imsmanifest.xml' => <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<manifest>
    <resources>
        <resource identifier="bad" href="../escape-probe.html" />
    </resources>
</manifest>
XML,
        'index_lms.html' => '<html><head></head><body>inside release</body></html>',
    ]);

    try {
        $result = app(ScormImporter::class)->importToFolder($upload, $targetPath);

        expect(File::get($escapeProbe))->not->toContain('/scorm_core/js/API.js');
        expect($result->relative_index_path)->toContain('/index_lms.html');
        expect($result->relative_index_path)->not->toContain('..');
    } finally {
        File::deleteDirectory(public_path('modules/test_scorm_security'));
    }
});

it('rejects zip entries with parent directory segments', function () {
    $targetPath = 'modules/test_scorm_security/'.Str::uuid();
    $upload = makeScormImporterSecurityUpload([
        '../escape.txt' => 'outside',
        'imsmanifest.xml' => '<manifest />',
    ]);

    try {
        expect(fn () => app(ScormImporter::class)->importToFolder($upload, $targetPath))
            ->toThrow(RuntimeException::class, 'chemin non autorisé');
    } finally {
        File::deleteDirectory(public_path('modules/test_scorm_security'));
    }
});
