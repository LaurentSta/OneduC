<?php

use App\Http\Controllers\Backend\ScormV2Controller;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::post('/lectures/{lecture}/scorm-v2', [ScormV2Controller::class, 'upload'])
        ->name('admin.lectures.scormv2.upload');

    Route::get('/lectures/{lecture}/scorm-v2/preview', [ScormV2Controller::class, 'preview'])
        ->name('admin.lectures.scormv2.preview');
});
