<?php

use App\Support\LearningAssetPath;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class);

it('resolves relative section video to modules/videos when file exists', function () {
    $relative = 'unit_' . Str::uuid() . '.mp4';
    $path = public_path('modules/videos/' . $relative);

    File::ensureDirectoryExists(dirname($path));
    File::put($path, 'fake-video');

    try {
        expect(LearningAssetPath::resolveSectionVideoUrl($relative))
            ->toBe(asset('modules/videos/' . $relative));
    } finally {
        File::delete($path);
    }
});

it('falls back to legacy section video path when new path is missing', function () {
    $relative = 'unit_' . Str::uuid() . '.mp4';
    $legacyPath = public_path('modules/scorm/02_videos/' . $relative);

    File::ensureDirectoryExists(dirname($legacyPath));
    File::put($legacyPath, 'legacy-video');

    try {
        expect(LearningAssetPath::resolveSectionVideoUrl($relative))
            ->toBe(asset('modules/scorm/02_videos/' . $relative));
    } finally {
        File::delete($legacyPath);
    }
});

it('passes through absolute urls and absolute paths for videos', function () {
    expect(LearningAssetPath::resolveModuleVideoUrl('https://cdn.example.com/video.mp4'))
        ->toBe('https://cdn.example.com/video.mp4');

    expect(LearningAssetPath::resolveModuleVideoUrl('/modules/videos/local.mp4'))
        ->toBe('/modules/videos/local.mp4');
});

it('resolves evaluation index path to new location when file exists', function () {
    $folder = 'eval_' . Str::uuid();
    $path = public_path("modules/evaluations/scorm/{$folder}/res/index.html");

    File::ensureDirectoryExists(dirname($path));
    File::put($path, '<html></html>');

    try {
        expect(LearningAssetPath::resolveEvaluationIndexRelativePath($folder))
            ->toBe("modules/evaluations/scorm/{$folder}/res/index.html");
    } finally {
        File::delete($path);
    }
});

it('falls back to legacy evaluation path when new path is missing', function () {
    $folder = 'eval_' . Str::uuid();
    $legacyPath = public_path("modules/scorm/01_evaluations/{$folder}/res/index.html");

    File::ensureDirectoryExists(dirname($legacyPath));
    File::put($legacyPath, '<html></html>');

    try {
        expect(LearningAssetPath::resolveEvaluationIndexRelativePath($folder))
            ->toBe("modules/scorm/01_evaluations/{$folder}/res/index.html");
    } finally {
        File::delete($legacyPath);
    }
});
