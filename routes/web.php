<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\ModuleController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ContactController;


// -------------------------------------------------------------------------
// Fichier de routes publiques de l'application. Les routes des différents
// rôles (admin, formateur, stagiaire) sont importées plus bas.
// -------------------------------------------------------------------------
// ----------------------------------------------------------
// 🌐 Pages publiques générales
// ----------------------------------------------------------
// Page d'accueil du site
Route::get('/', [\App\Http\Controllers\UserController::class, 'Index'])->name('index');
// Présentation du projet Onéduc
Route::get('/le-projet-oneduc-fr', [\App\Http\Controllers\UserController::class, 'Projet'])->name('projet');
// Présentation de l'association
Route::get('/association', [\App\Http\Controllers\UserController::class, 'Association'])->name('association');
// Page d'adhésion pour rejoindre l'association
Route::get('/adhesion', [\App\Http\Controllers\UserController::class, 'Adhesion'])->name('adhesion');
// Affiche les modules liés à une catégorie (accessible sans connexion)
Route::get('/categorie/{id}/modules', [\App\Http\Controllers\Backend\CategoryController::class, 'showCategoryModules'])->name('frontend.category.modules');
// --- Pages légales et conformité ---
Route::view('/mentions-legales', 'frontend.contenu.mentions-legales')->name('mentions-legales');
Route::view('/conditions-utilisation', 'frontend.contenu.conditions-utilisation')->name('conditions-utilisation');
Route::view('/confidentialite', 'frontend.contenu.confidentialite')->name('confidentialite');
Route::view('/cookies', 'frontend.contenu.cookies')->name('cookies');

// ----------------------------------------------------------
// 🧠 Catégories & sous-catégories
// ----------------------------------------------------------
// Liste l'ensemble des catégories de formation
Route::get('/formations', [\App\Http\Controllers\Backend\CategoryController::class, 'FrontCategories'])->name('categories.all');
// Affiche les sous-catégories d'une catégorie donnée
Route::get('/categorie/{id}/sous-categories', [\App\Http\Controllers\Backend\CategoryController::class, 'showSubCategories'])->name('frontend.subcategory.modules');

// Route::get('/categories/{id}/subcategories', [\App\Http\Controllers\Backend\CategoryController::class, 'showSubCategories'])->name('frontend.subcategories');
// Route::get('/categories/{id}/modules', [\App\Http\Controllers\Backend\CategoryController::class, 'showCategoryModules'])->name('frontend.category.modules');

// ----------------------------------------------------------
// 📚 Modules de formation
// ----------------------------------------------------------
// Liste des modules de formation disponibles
Route::get('/MFormations', [\App\Http\Controllers\Frontend\MFormationsController::class, 'index'])->name('frontend.modules.index');
// Détail d'un module de formation
Route::get('/modules/{id}', [\App\Http\Controllers\Frontend\MFormationsController::class, 'show'])->name('frontend.modules.show');
// ----------------------------------------------------------
// 📚 Evaluation
// ----------------------------------------------------------
// Les évaluations sont protégées par l'authentification
Route::middleware(['auth'])->group(function () {
    Route::get('/evaluations/{id}', [\App\Http\Controllers\Backend\EvaluationController::class, 'show'])->name('evaluation.show');
});
Route::middleware(['auth'])->group(function () {
    Route::get('/evaluations/{evaluation}/fin',
        [\App\Http\Controllers\Backend\EvaluationController::class, 'fin']
    )->name('stagiaire.evaluations.fin');
});
// ----------------------------------------------------------
// 🎥 Lecture d’une leçon
// ----------------------------------------------------------
// Affiche une leçon précise
Route::get('/lecture/{id}', [\App\Http\Controllers\Frontend\LectureController::class, 'show'])->name('lecture.show');




// ----------------------------------------------------------
// 🔐 Authentification
// ----------------------------------------------------------
// Formulaire de connexion
Route::get('/connexion', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('connexion');
// Traitement de la connexion
Route::post('/connexion', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])->name('login.process');
// Déconnexion de l'utilisateur
Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
// Formulaire d'inscription classique
Route::get('/inscription', [\App\Http\Controllers\UserController::class, 'Register'])->name('inscription');

// Envoi de feedback sur une leçon (uniquement pour les utilisateurs connectés)
Route::middleware(['auth'])->group(function () {
    Route::post('/lecture/{id}/valider', [\App\Http\Controllers\Frontend\LectureController::class, 'valider'])->name('lecture.valider');
    Route::post('/feedback', [\App\Http\Controllers\LessonFeedbackController::class, 'store'])->name('feedback.store');
    Route::get('/feedback/{lesson}', [\App\Http\Controllers\LessonFeedbackController::class, 'index'])->name('feedback.index');
});

// ----------------------------------------------------------
// 🟢 Admin
// ----------------------------------------------------------
// Routes spécifiques au rôle administrateur
require __DIR__.'/admin.php';

// ----------------------------------------------------------
// 🟠 Formateur
// ----------------------------------------------------------
// Routes dédiées aux formateurs
require __DIR__.'/formateur.php';

// ----------------------------------------------------------
// 🟣 Stagiaire
// ----------------------------------------------------------
// Routes accessibles aux stagiaires
require __DIR__.'/stagiaire.php';

// ----------------------------------------------------------
// ----------------------------------------------------------
// 🟣 Scorm
// ----------------------------------------------------------
// Routes accessibles aux scorm V2
require __DIR__.'/scorm_v2.php';

// ----------------------------------------------------------
// Api
// ----------------------------------------------------------
// Endpoints API divers
require __DIR__.'/api.php';

// ----------------------------------------------------------
// Inscription formateur
// ----------------------------------------------------------
// Formulaire d'inscription dédié aux formateurs
Route::get('/inscription-formateur', function () {
    return view('frontend.formateur.reg_formateur');
})->name('formateur.inscription.form');
// Traitement de l'inscription formateur
Route::post('/inscription-formateur', [\App\Http\Controllers\FormateurController::class, 'register'])->name('formateur.inscription');

// Connexion via code d’accès (stagiaire)
// Connexion d'un stagiaire via un code d'accès fourni par le formateur
Route::get('/stagiaire/connexion-code', [\App\Http\Controllers\UserController::class, 'showCodeLoginForm'])->name('stagiaire.code.form');
Route::post('/stagiaire/connexion-code', [\App\Http\Controllers\UserController::class, 'loginByCode'])->name('stagiaire.code.login');

// Formulaire de contact
Route::middleware(['throttle:contact'])->group(function () {
    Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
});
// Affichage du formulaire de contact
Route::get('/contact', fn () => view('frontend.contenu.contact'))->name('contact.form');
Route::get('/contact', [ContactController::class, 'index'])->name('contact'); // GET pour la page


// Auth Laravel (Jetstream/Breeze)
// Routes d'authentification fournies par Laravel Breeze/Jetstream
require __DIR__.'/auth.php';
