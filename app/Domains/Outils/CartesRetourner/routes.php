<?php

use App\Domains\Outils\CartesRetourner\Http\Controllers\Formateur\CartesRetournerCarteController;
use App\Domains\Outils\CartesRetourner\Http\Controllers\Formateur\CartesRetournerController;
use App\Domains\Outils\CartesRetourner\Http\Controllers\Stagiaire\ParticipationCartesRetournerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:formateur', 'association.member'])
    ->prefix('formateur/cartes-retourner')
    ->name('formateur.cartes-retourner.')
    ->group(function (): void {
        Route::get('/', [CartesRetournerController::class, 'index'])->name('index');
        Route::post('/', [CartesRetournerController::class, 'store'])->name('store');
        Route::get('/{sessionId}', [CartesRetournerController::class, 'show'])->whereNumber('sessionId')->name('show');
        Route::post('/{sessionId}/toggle', [CartesRetournerController::class, 'toggle'])->whereNumber('sessionId')->name('toggle');
        Route::delete('/{sessionId}', [CartesRetournerController::class, 'destroy'])->whereNumber('sessionId')->name('destroy');

        Route::post('/{sessionId}/cartes', [CartesRetournerCarteController::class, 'store'])->whereNumber('sessionId')->name('cartes.store');
        Route::delete('/{sessionId}/cartes/{carteId}', [CartesRetournerCarteController::class, 'destroy'])->whereNumber(['sessionId', 'carteId'])->name('cartes.destroy');
        Route::post('/{sessionId}/cartes/{carteId}/move', [CartesRetournerCarteController::class, 'move'])->whereNumber(['sessionId', 'carteId'])->name('cartes.move');
    });

Route::middleware(['auth'])
    ->prefix('oneduc/cartes-retourner')
    ->name('cartes-retourner.')
    ->group(function (): void {
        Route::get('/', [ParticipationCartesRetournerController::class, 'home'])->name('join');
        Route::post('/', [ParticipationCartesRetournerController::class, 'resolveCode'])->name('resolve');
        Route::get('/{code}', [ParticipationCartesRetournerController::class, 'joinByCode'])->name('join.code');
    });
