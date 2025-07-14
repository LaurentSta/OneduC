<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StagiaireController;

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

});
