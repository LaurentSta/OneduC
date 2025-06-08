<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SCORMController;
use App\Http\Controllers\Frontend\LectureController;
use App\Http\Controllers\ScormInteractionController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Http\Controllers\EvaluationSCORMController;



// Routes SCORM (ouvertes ou protégées selon usage)
Route::post('/scorm/save-progress', [SCORMController::class, 'saveProgress'])
    ->withoutMiddleware([VerifyCsrfToken::class]);

Route::post('/scorm/progress', [SCORMController::class, 'saveProgress'])
    ->middleware('auth');

Route::get('/lecture/{id}/scorm', [LectureController::class, 'showScorm'])->name('lecture.scorm');



Route::post('/scorm/evaluation-progress', [EvaluationSCORMController::class, 'saveEvaluationProgress'])
->withoutMiddleware([VerifyCsrfToken::class]);


