<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormateurController;
use App\Http\Controllers\Formateur\GroupeController;
use App\Http\Controllers\Formateur\ProgressionController;

Route::middleware(['auth', 'role:formateur'])->prefix('formateur')->name('formateur.')->group(function () {

    // 🖥️ Dashboard & profil
    Route::get('/', [FormateurController::class, 'FormateurDashboard'])->name('dashboard');
    Route::get('/profile', [FormateurController::class, 'FormateurProfile'])->name('profile');
    Route::get('/parametre', [FormateurController::class, 'FormateurParametre'])->name('parametre');
    Route::post('/profil/store', [FormateurController::class, 'FormateurProfilStore'])->name('profil.store');
    Route::get('/securite', [FormateurController::class, 'showFormateurSecurite'])->name('securite.show');
    Route::post('/securite', [FormateurController::class, 'FormateurSecurite'])->name('securite');

    // 👤 Stagiaires
    Route::get('/stagiaires', [FormateurController::class, 'indexStagiaires'])->name('stagiaires.index');
    Route::get('/stagiaires/create', [FormateurController::class, 'createStagiaire'])->name('stagiaires.create');
    Route::post('/stagiaires', [FormateurController::class, 'storeStagiaire'])->name('stagiaires.store');
    Route::get('/stagiaires/{id}/edit', [FormateurController::class, 'editStagiaire'])->name('stagiaires.edit');
    Route::put('/stagiaires/{id}', [FormateurController::class, 'updateStagiaire'])->name('stagiaires.update');
    Route::delete('/stagiaires/{id}', [FormateurController::class, 'destroyStagiaire'])->name('stagiaires.destroy');


    // 🧑‍🤝‍🧑 Groupes (wizard)
    Route::get('/groupes/create', [GroupeController::class, 'create'])->name('groupes.create');
    Route::post('/groupes', [GroupeController::class, 'store'])->name('groupes.store');
    Route::get('/groupes/{id}/edit', [GroupeController::class, 'edit'])->name('groupes.edit');
    Route::put('/groupes/{id}', [GroupeController::class, 'update'])->name('groupes.update');
    Route::delete('/groupes/{id}', [GroupeController::class, 'destroy'])->name('groupes.destroy');


    // 📂 Groupes (liste)
    Route::get('/groupes', [GroupeController::class, 'index'])->name('groupes.index');

    // 📈 Progression des stagiaires
    Route::get('/progressions', [ProgressionController::class, 'index'])->name('progressions.index');
    Route::post('/progression/complete', [ProgressionController::class, 'markCompleted'])->name('progression.complete');

    // 📂 Mes formations
    Route::get('/formations', [FormateurController::class, 'mesModules'])->name('formations.index');


});
