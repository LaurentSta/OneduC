<?php

use App\Domains\Outils\Memoire\Http\Controllers\Formateur\MemoireController;
use App\Domains\Outils\Memoire\Http\Controllers\Stagiaire\ParticipationMemoireController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:formateur', 'association.member'])
    ->prefix('formateur/memoire')
    ->name('formateur.memoire.')
    ->group(function (): void {
        Route::get('/', [MemoireController::class, 'index'])->name('index');
        Route::post('/', [MemoireController::class, 'store'])->name('store');
        Route::get('/{sessionId}', [MemoireController::class, 'show'])->whereNumber('sessionId')->name('show');
        Route::post('/{sessionId}/toggle', [MemoireController::class, 'toggle'])->whereNumber('sessionId')->name('toggle');
        Route::get('/{sessionId}/state', [MemoireController::class, 'state'])->whereNumber('sessionId')->name('state');
        Route::delete('/{sessionId}', [MemoireController::class, 'destroy'])->whereNumber('sessionId')->name('destroy');
    });

Route::middleware(['auth'])
    ->prefix('oneduc/memoire')
    ->name('memoire.')
    ->group(function (): void {
        Route::get('/', [ParticipationMemoireController::class, 'home'])->name('join');
        Route::post('/', [ParticipationMemoireController::class, 'resolveCode'])->name('resolve');
        Route::get('/{code}', [ParticipationMemoireController::class, 'joinByCode'])->name('join.code');
        Route::post('/{code}/reponses', [ParticipationMemoireController::class, 'submit'])
            ->middleware('throttle:30,1')
            ->name('submit');
    });
