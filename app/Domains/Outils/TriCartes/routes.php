<?php

use App\Domains\Outils\TriCartes\Http\Controllers\Formateur\TriCartesCarteController;
use App\Domains\Outils\TriCartes\Http\Controllers\Formateur\TriCartesCategorieController;
use App\Domains\Outils\TriCartes\Http\Controllers\Formateur\TriCartesController;
use App\Domains\Outils\TriCartes\Http\Controllers\Stagiaire\ParticipationTriCartesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:formateur', 'association.member'])
    ->prefix('formateur/tri-cartes')
    ->name('formateur.tri-cartes.')
    ->group(function (): void {
        Route::get('/', [TriCartesController::class, 'index'])->name('index');
        Route::post('/', [TriCartesController::class, 'store'])->name('store');
        Route::get('/{sessionId}', [TriCartesController::class, 'show'])->whereNumber('sessionId')->name('show');
        Route::post('/{sessionId}/toggle', [TriCartesController::class, 'toggle'])->whereNumber('sessionId')->name('toggle');
        Route::get('/{sessionId}/state', [TriCartesController::class, 'state'])->whereNumber('sessionId')->name('state');
        Route::delete('/{sessionId}', [TriCartesController::class, 'destroy'])->whereNumber('sessionId')->name('destroy');

        Route::post('/{sessionId}/categories', [TriCartesCategorieController::class, 'store'])->whereNumber('sessionId')->name('categories.store');
        Route::post('/{sessionId}/categories/{categorieId}', [TriCartesCategorieController::class, 'update'])->whereNumber(['sessionId', 'categorieId'])->name('categories.update');
        Route::delete('/{sessionId}/categories/{categorieId}', [TriCartesCategorieController::class, 'destroy'])->whereNumber(['sessionId', 'categorieId'])->name('categories.destroy');

        Route::post('/{sessionId}/cartes', [TriCartesCarteController::class, 'store'])->whereNumber('sessionId')->name('cartes.store');
        Route::delete('/{sessionId}/cartes/{carteId}', [TriCartesCarteController::class, 'destroy'])->whereNumber(['sessionId', 'carteId'])->name('cartes.destroy');
        Route::post('/{sessionId}/cartes/{carteId}/move', [TriCartesCarteController::class, 'move'])->whereNumber(['sessionId', 'carteId'])->name('cartes.move');
    });

Route::middleware(['auth'])
    ->prefix('oneduc/tri-cartes')
    ->name('tri-cartes.')
    ->group(function (): void {
        Route::get('/', [ParticipationTriCartesController::class, 'home'])->name('join');
        Route::post('/', [ParticipationTriCartesController::class, 'resolveCode'])->name('resolve');
        Route::get('/{code}', [ParticipationTriCartesController::class, 'joinByCode'])->name('join.code');
        Route::post('/{code}/submit', [ParticipationTriCartesController::class, 'submit'])
            ->middleware('throttle:60,1')
            ->name('submit');
    });
