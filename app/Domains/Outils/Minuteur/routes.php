<?php

use App\Domains\Outils\Minuteur\Http\Controllers\Formateur\TimerController as FormateurTimerController;
use App\Domains\Outils\Minuteur\Http\Controllers\Stagiaire\TimerController as StagiaireTimerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:formateur', 'association.member'])
    ->prefix('formateur/groupes/{group}/minuteur')
    ->name('formateur.groupes.timer.')
    ->group(function () {
        Route::get('/', [FormateurTimerController::class, 'show'])->name('show');
        Route::get('/status', [FormateurTimerController::class, 'status'])->name('status');
        Route::post('/configure', [FormateurTimerController::class, 'configure'])->name('configure');
        Route::post('/start', [FormateurTimerController::class, 'start'])->name('start');
        Route::post('/pause', [FormateurTimerController::class, 'pause'])->name('pause');
        Route::post('/reset', [FormateurTimerController::class, 'reset'])->name('reset');
    });

Route::middleware(['auth', 'role:stagiaire', 'track.time', 'force.password.change'])
    ->prefix('stagiaire/minuteur/groupes/{group}')
    ->name('stagiaire.timer.')
    ->group(function () {
        Route::get('/', [StagiaireTimerController::class, 'show'])->name('show');
        Route::get('/status', [StagiaireTimerController::class, 'status'])->name('status');
    });
