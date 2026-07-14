<?php

use App\Domains\Outils\Carrousel\Http\Controllers\Formateur\CarrouselController;
use App\Domains\Outils\Carrousel\Http\Controllers\Formateur\CarrouselSlideController;
use App\Domains\Outils\Carrousel\Http\Controllers\Stagiaire\ParticipationCarrouselController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:formateur', 'association.member'])
    ->prefix('formateur/carrousel')
    ->name('formateur.carrousel.')
    ->group(function (): void {
        Route::get('/', [CarrouselController::class, 'index'])->name('index');
        Route::post('/', [CarrouselController::class, 'store'])->name('store');
        Route::get('/{sessionId}', [CarrouselController::class, 'show'])->whereNumber('sessionId')->name('show');
        Route::post('/{sessionId}/toggle', [CarrouselController::class, 'toggle'])->whereNumber('sessionId')->name('toggle');
        Route::delete('/{sessionId}', [CarrouselController::class, 'destroy'])->whereNumber('sessionId')->name('destroy');

        Route::post('/{sessionId}/slides', [CarrouselSlideController::class, 'store'])->whereNumber('sessionId')->name('slides.store');
        Route::post('/{sessionId}/slides/{slideId}', [CarrouselSlideController::class, 'update'])->whereNumber(['sessionId', 'slideId'])->name('slides.update');
        Route::delete('/{sessionId}/slides/{slideId}', [CarrouselSlideController::class, 'destroy'])->whereNumber(['sessionId', 'slideId'])->name('slides.destroy');
        Route::post('/{sessionId}/slides/{slideId}/move', [CarrouselSlideController::class, 'move'])->whereNumber(['sessionId', 'slideId'])->name('slides.move');
    });

Route::middleware(['auth'])
    ->prefix('oneduc/carrousel')
    ->name('carrousel.')
    ->group(function (): void {
        Route::get('/', [ParticipationCarrouselController::class, 'home'])->name('join');
        Route::post('/', [ParticipationCarrouselController::class, 'resolveCode'])->name('resolve');
        Route::get('/{code}', [ParticipationCarrouselController::class, 'joinByCode'])->name('join.code');
    });
