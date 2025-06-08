<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\ModuleController;
// ----------------------------------------------------------
// 🌐 Pages publiques générales
// ----------------------------------------------------------
Route::get('/', [\App\Http\Controllers\UserController::class, 'Index'])->name('index');
Route::get('/le-projet-oneduc-fr', [\App\Http\Controllers\UserController::class, 'Projet'])->name('projet');
Route::get('/association', [\App\Http\Controllers\UserController::class, 'Association'])->name('association');
Route::get('/adhesion', [\App\Http\Controllers\UserController::class, 'Adhesion'])->name('adhesion');
// Route pour afficher les modules liés à une catégorie (public)
Route::get('/categorie/{id}/modules', [\App\Http\Controllers\Backend\CategoryController::class, 'showCategoryModules'])->name('frontend.category.modules');
// --- Pages légales et conformité ---
Route::view('/mentions-legales', 'frontend.contenu.mentions-legales')->name('mentions-legales');
Route::view('/conditions-utilisation', 'frontend.contenu.conditions-utilisation')->name('conditions-utilisation');
Route::view('/confidentialite', 'frontend.contenu.confidentialite')->name('confidentialite');
Route::view('/cookies', 'frontend.contenu.cookies')->name('cookies');

// ----------------------------------------------------------
// 🧠 Catégories & sous-catégories
// ----------------------------------------------------------
// Route publique (non protégée par auth)
Route::get('/formations', [\App\Http\Controllers\Backend\CategoryController::class, 'FrontCategories'])->name('categories.all');
// Route pour voir les sous-catégories d’une catégorie (public)
Route::get('/categorie/{id}/sous-categories', [\App\Http\Controllers\Backend\CategoryController::class, 'showSubCategories'])->name('frontend.subcategory.modules');

// Route::get('/categories/{id}/subcategories', [\App\Http\Controllers\Backend\CategoryController::class, 'showSubCategories'])->name('frontend.subcategories');
// Route::get('/categories/{id}/modules', [\App\Http\Controllers\Backend\CategoryController::class, 'showCategoryModules'])->name('frontend.category.modules');

// ----------------------------------------------------------
// 📚 Modules de formation
// ----------------------------------------------------------
Route::get('/MFormations', [\App\Http\Controllers\Frontend\MFormationsController::class, 'index'])->name('frontend.modules.index');
Route::get('/modules/{id}', [\App\Http\Controllers\Frontend\MFormationsController::class, 'show'])->name('frontend.modules.show');
// ----------------------------------------------------------
// 📚 Evaluation
// ----------------------------------------------------------
Route::middleware(['auth'])->group(function () {
Route::get('/evaluations/{id}', [\App\Http\Controllers\Backend\EvaluationController::class, 'show'])->name('evaluation.show');
});

// ----------------------------------------------------------
// 🎥 Lecture d’une leçon
// ----------------------------------------------------------
Route::get('/lecture/{id}', [\App\Http\Controllers\Frontend\LectureController::class, 'show'])->name('lecture.show');
Route::get('/formation/module/{id}/lecture', [\App\Http\Controllers\Backend\ModuleController::class, 'lire'])->name('module.lecture');
// ----------------------------------------------------------
// 🎥 Lecture d’une section
// ----------------------------------------------------------
Route::get('/module/{id}/section/{section_id}', [ModuleController::class, 'section'])->name('module.section');


// ----------------------------------------------------------
// 🔐 Authentification
// ----------------------------------------------------------
Route::get('/connexion', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('connexion');
Route::post('/connexion', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])->name('login.process');
Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
Route::get('/inscription', [\App\Http\Controllers\UserController::class, 'Register'])->name('inscription');

// Feedback public
Route::middleware(['auth'])->group(function () {
    Route::post('/lecture/{id}/valider', [\App\Http\Controllers\Frontend\LectureController::class, 'valider'])->name('lecture.valider');
    Route::post('/feedback', [\App\Http\Controllers\LessonFeedbackController::class, 'store'])->name('feedback.store');
    Route::get('/feedback/{lesson}', [\App\Http\Controllers\LessonFeedbackController::class, 'index'])->name('feedback.index');
});

// ----------------------------------------------------------
// 🟢 Admin
// ----------------------------------------------------------
require __DIR__.'/admin.php';

// ----------------------------------------------------------
// 🟠 Formateur
// ----------------------------------------------------------
require __DIR__.'/formateur.php';

// ----------------------------------------------------------
// 🟣 Stagiaire
// ----------------------------------------------------------
require __DIR__.'/stagiaire.php';

// ----------------------------------------------------------
// SCORM
// ----------------------------------------------------------
require __DIR__.'/scorm.php';

// ----------------------------------------------------------
// Api
// ----------------------------------------------------------
require __DIR__.'/api.php';

// ----------------------------------------------------------
// Inscription formateur
// ----------------------------------------------------------
Route::get('/inscription-formateur', function () {
    return view('frontend.formateur.reg_formateur');
})->name('formateur.inscription.form');
Route::post('/inscription-formateur', [\App\Http\Controllers\FormateurController::class, 'register'])->name('formateur.inscription');

// Connexion via code d’accès (stagiaire)
Route::get('/stagiaire/connexion-code', [\App\Http\Controllers\UserController::class, 'showCodeLoginForm'])->name('stagiaire.code.form');
Route::post('/stagiaire/connexion-code', [\App\Http\Controllers\UserController::class, 'loginByCode'])->name('stagiaire.code.login');

// Formulaire de contact
Route::view('/contact', 'frontend.contenu.contact')->name('contact');
Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'send'])->name('contact.send');




// Auth Laravel (Jetstream/Breeze)
require __DIR__.'/auth.php';
