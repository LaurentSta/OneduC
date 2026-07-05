<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SCORMController;
use App\Http\Controllers\ContentBlockScormController;
use App\Http\Controllers\Frontend\LectureController;
use App\Http\Controllers\ScormInteractionController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Http\Controllers\EvaluationSCORMController;



// Routes SCORM (ouvertes ou protégées selon usage)
Route::post('/scorm/save-progress', [SCORMController::class, 'saveProgress'])
    ->withoutMiddleware([VerifyCsrfToken::class]);

Route::post('/scorm/progress', [SCORMController::class, 'saveProgress'])
    ->middleware('auth');

Route::post('/scorm/save-block-progress', [ContentBlockScormController::class, 'saveProgress'])
    ->withoutMiddleware([VerifyCsrfToken::class]);

Route::get('/lecture/{id}/scorm', [LectureController::class, 'showScorm'])->name('lecture.scorm');
Route::get('/lecture/{id}/scorm-block/{key}', [LectureController::class, 'showScormBlock'])
    ->middleware('auth')
    ->name('lecture.scorm-block');
Route::get('/lecture/{id}/slides', [LectureController::class, 'showSlides'])->name('lecture.slides');



Route::post('/scorm/evaluation-progress', [EvaluationSCORMController::class, 'saveEvaluationProgress'])
->withoutMiddleware([VerifyCsrfToken::class]);

