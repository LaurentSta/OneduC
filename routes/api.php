<?php

use App\Http\Controllers\VideoSegmentTrackingController;

Route::middleware('auth')->post('/video-segment', [VideoSegmentTrackingController::class, 'store'])->name('api.video.segment');



