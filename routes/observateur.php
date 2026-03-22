<?php

use App\Http\Controllers\Backend\ModuleController;
use App\Http\Controllers\Observateur\GroupeController;
use App\Http\Controllers\Observateur\ProgressionController;
use App\Http\Controllers\ObservateurController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:observateur'])
    ->prefix('observateur')
    ->name('observateur.')
    ->group(function () {
        Route::get('/', [ObservateurController::class, 'dashboard'])->name('dashboard');

        Route::get('/groupes', [GroupeController::class, 'index'])->name('groupes.index');
        Route::get('/groupes/{group}/modules/{module}/lecons', [GroupeController::class, 'showModuleLessons'])
            ->name('groupes.modules.lecons.show');

        Route::get('/progressions', fn () => redirect()->route('observateur.progressions.groupes'))
            ->name('progressions.index');
        Route::get('/progressions/groupes', [ProgressionController::class, 'index'])
            ->name('progressions.groupes')
            ->defaults('view', 'groupes');
        Route::get('/progressions/stagiaires', [ProgressionController::class, 'index'])
            ->name('progressions.stagiaires')
            ->defaults('view', 'stagiaires');
        Route::get('/progressions/stagiaire/{user}', [ProgressionController::class, 'index'])
            ->name('progressions.stagiaire')
            ->defaults('view', 'stagiaire');

        Route::get('/formations/{module}/section/{section}', [ModuleController::class, 'section'])
            ->name('formations.section');
        Route::get('/formations/{module}/section/{section}/lesson/{lecture}', [ModuleController::class, 'lire'])
            ->name('formations.lecture');
    });
