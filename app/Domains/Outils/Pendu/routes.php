<?php

use App\Domains\Outils\Pendu\Http\Controllers\Formateur\PenduController;
use App\Domains\Outils\Pendu\Http\Controllers\Stagiaire\ParticipationPenduController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:formateur', 'association.member'])
    ->prefix('formateur/pendu')
    ->name('formateur.pendu.')
    ->group(function (): void {
        Route::get('/', [PenduController::class, 'index'])->name('index');
        Route::post('/', [PenduController::class, 'store'])->name('store');
        Route::get('/{sessionId}', [PenduController::class, 'show'])->whereNumber('sessionId')->name('show');
        Route::post('/{sessionId}/toggle', [PenduController::class, 'toggle'])->whereNumber('sessionId')->name('toggle');
        Route::get('/{sessionId}/state', [PenduController::class, 'state'])->whereNumber('sessionId')->name('state');
        Route::delete('/{sessionId}', [PenduController::class, 'destroy'])->whereNumber('sessionId')->name('destroy');
    });

Route::middleware(['auth'])
    ->prefix('oneduc/pendu')
    ->name('pendu.')
    ->group(function (): void {
        Route::get('/', [ParticipationPenduController::class, 'home'])->name('join');
        Route::post('/', [ParticipationPenduController::class, 'resolveCode'])->name('resolve');
        Route::get('/{code}', [ParticipationPenduController::class, 'joinByCode'])->name('join.code');
        Route::post('/{code}/lettre', [ParticipationPenduController::class, 'submit'])
            ->middleware('throttle:60,1')
            ->name('submit');
        Route::get('/{code}/data', [ParticipationPenduController::class, 'data'])
            ->middleware('throttle:120,1')
            ->name('data');
    });
