<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LessonFeedbackController;

Route::middleware(['auth'])->group(function () {
    Route::post('/feedback', [LessonFeedbackController::class, 'store'])->name('feedback.store');
    Route::get('/feedback/{lesson}', [LessonFeedbackController::class, 'index'])->name('feedback.index');


});
