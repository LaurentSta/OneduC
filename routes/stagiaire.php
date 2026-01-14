<?php
// routes/stagiaire.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StagiaireController;
use App\Http\Controllers\Backend\ModuleController;
use App\Http\Controllers\Stagiaire\QuizController;

Route::middleware(['auth', 'role:stagiaire', 'track.time'])
    ->prefix('stagiaire')
    ->name('stagiaire.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard & profil
        |--------------------------------------------------------------------------
        */
        Route::get('/', [StagiaireController::class, 'StagiaireDashboard'])
            ->name('dashboard');

        Route::get('/profile', [UserController::class, 'UserProfile'])
            ->name('profile');

        Route::get('/parametre', [UserController::class, 'UserParametre'])
            ->name('parametre');

        Route::post('/profil/store', [UserController::class, 'UserProfilStore'])
            ->name('profil.store');

        Route::match(['get', 'post'], '/securite', [UserController::class, 'showUserSecurite'])
            ->name('securite.show');

        /*
        |--------------------------------------------------------------------------
        | Modules & progression
        |--------------------------------------------------------------------------
        */
        Route::get('/modules', [StagiaireController::class, 'StagiaireModules'])
            ->name('modules');

        Route::get('/modules/{module}', [StagiaireController::class, 'StagiaireModuleDetail'])
            ->name('module.detail');

        Route::get('/modules/{module}/progression-json', [UserController::class, 'getProgressionJson'])
            ->name('module.progression.json');

        Route::get('/resultats', [StagiaireController::class, 'StagiaireResultats'])
            ->name('resultats');

        Route::get('/progression/detailmodule', [StagiaireController::class, 'ProgressionDetailModule'])
            ->name('progression.detailmodule');

        Route::get('/modules/{module}/fin', [ModuleController::class, 'finModule'])
            ->name('module.fin');

        /*
        |--------------------------------------------------------------------------
        | Sections & leçons
        |--------------------------------------------------------------------------
        */
        Route::get('/modules/{module}/sections/{section}', [ModuleController::class, 'section'])
            ->name('module.section');

        Route::get('/modules/{module}/sections/{section}/lessons/{lecture}', [ModuleController::class, 'lire'])
            ->name('module.lecture');

        /*
        |--------------------------------------------------------------------------
        | Quiz DANS la leçon (URL hiérarchique)
        |--------------------------------------------------------------------------
        */
        // Quiz (start signé)
        Route::get('/modules/{module}/sections/{section}/lessons/{lecture}/quiz/start', [QuizController::class, 'start'])
            ->name('quiz.start')
            ->middleware('signed');

        // Quiz imbriqué dans la leçon
        Route::prefix('/modules/{module}/sections/{section}/lessons/{lecture}/quiz/{attempt}')
        ->name('lesson.quiz.')
        ->group(function () {
            Route::get('/question', [QuizController::class, 'showQuestion'])->name('question');
            Route::post('/answer', [QuizController::class, 'answer'])->name('answer');
            Route::get('/result', [QuizController::class, 'result'])->name('result');

            // NOUVEAU : recommencer
            Route::post('/restart', [QuizController::class, 'restart'])->name('restart');
        });


    });
