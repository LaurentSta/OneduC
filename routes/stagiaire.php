<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StagiaireController;
use App\Http\Controllers\Backend\ModuleController;
use App\Http\Controllers\Stagiaire\QuizController;

Route::middleware(['auth', 'role:stagiaire', 'track.time'])
    ->prefix('stagiaire')
    ->name('stagiaire.')
    ->group(function () {

        // Dashboard & profil
        Route::get('/', [StagiaireController::class, 'StagiaireDashboard'])->name('dashboard');
        Route::get('/profile', [UserController::class, 'UserProfile'])->name('profile');
        Route::get('/parametre', [UserController::class, 'UserParametre'])->name('parametre');
        Route::post('/profil/store', [UserController::class, 'UserProfilStore'])->name('profil.store');
        Route::match(['get', 'post'], '/securite', [UserController::class, 'showUserSecurite'])->name('securite.show');

        // Modules & résultats
        Route::get('/modules', [StagiaireController::class, 'StagiaireModules'])->name('modules');
        Route::get('/modules/{id}', [StagiaireController::class, 'StagiaireModuleDetail'])->name('module.detail');
        Route::get('/resultats', [StagiaireController::class, 'StagiaireResultats'])->name('resultats');

        // Section d’un module
        Route::get('/modules/{module}/sections/{section}', [ModuleController::class, 'section'])->name('module.section');

        // Leçon (unifier {lecture})
        Route::get('/modules/{module}/sections/{section}/lessons/{lecture}', [ModuleController::class, 'lire'])
            ->name('module.lecture');

        // JSON progression
        Route::get('/modules/{id}/progression-json', [UserController::class, 'getProgressionJson'])
            ->name('module.progression.json');

        // Détail progression (corriger le name)
        Route::get('/progression/detailmodule', [StagiaireController::class, 'ProgressionDetailModule'])
            ->name('progression.detailmodule');

        // Fin de module
        Route::get('/modules/{module}/fin', [ModuleController::class, 'finModule'])
            ->name('module.fin');

        // Quiz (signé)
        Route::get('/modules/{module}/sections/{section}/lessons/{lecture}/quiz/start', [QuizController::class, 'start'])
            ->name('quiz.start')
            ->middleware('signed');

        Route::get('/quiz/{attempt}/question', [QuizController::class, 'showQuestion'])->name('quiz.question');
        Route::post('/quiz/{attempt}/answer', [QuizController::class, 'answer'])->name('quiz.answer');
        Route::get('/quiz/{attempt}/result', [QuizController::class, 'result'])->name('quiz.result');
    });
