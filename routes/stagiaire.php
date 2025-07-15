<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StagiaireController;
use App\Http\Controllers\Backend\ModuleController;

Route::middleware(['auth', 'role:stagiaire', 'track.time'])->prefix('stagiaire')->name('stagiaire.')->group(function () {

    // 🖥️ Dashboard & profil
    Route::get('/', [StagiaireController::class, 'StagiaireDashboard'])->name('dashboard');
    Route::get('/profile', [UserController::class, 'UserProfile'])->name('profile');
    Route::get('/parametre', [UserController::class, 'UserParametre'])->name('parametre');
    Route::post('/profil/store', [UserController::class, 'UserProfilStore'])->name('profil.store');
    Route::match(['get', 'post'], '/securite', [UserController::class, 'showUserSecurite'])->name('securite.show');

    // 📚 Modules & résultats
    Route::get('/modules', [StagiaireController::class, 'StagiaireModules'])->name('modules');
    Route::get('/modules/{id}', [StagiaireController::class, 'StagiaireModuleDetail'])->name('module.detail');
    Route::get('/resultats', [StagiaireController::class, 'StagiaireResultats'])->name('resultats');

    // Lecture d'une section d’un module
    Route::get('/modules/{module}/sections/{section}', [ModuleController::class, 'section'])->name('module.section');

    // Lecture d'une leçon
    Route::get('/modules/{module}/sections/{section}/lessons/{lesson}', [ModuleController::class, 'lire'])->name('module.lecture');
});
