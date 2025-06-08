<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::middleware(['auth', 'role:stagiaire', 'track.time'])->prefix('stagiaire')->name('stagiaire.')->group(function () {

    // 🖥️ Dashboard & profil
    Route::get('/', [UserController::class, 'StagiaireDashboard'])->name('dashboard');
    Route::get('/profile', [UserController::class, 'UserProfile'])->name('profile');
    Route::get('/parametre', [UserController::class, 'UserParametre'])->name('parametre');
    Route::post('/profil/store', [UserController::class, 'UserProfilStore'])->name('profil.store');
   Route::match(['get', 'post'], '/securite', [UserController::class, 'showUserSecurite'])->name('securite.show');


    // 📚 Modules & résultats
    Route::get('/modules', [UserController::class, 'StagiaireModules'])->name('modules');
    Route::get('/resultats', [UserController::class, 'StagiaireResultats'])->name('resultats');

});
