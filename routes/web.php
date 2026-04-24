<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\ModuleController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Backend\StagiaireController;
use App\Http\Controllers\WordCloudParticipationController;
use App\Http\Controllers\RoueAleatoireParticipationController;
use App\Http\Controllers\QuestionWallParticipationController;

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
// Charte graphique publique
Route::view('/chartegraphique', 'frontend.contenu.charte_graphique')->name('charte-graphique');
// Affiche les modules liés à une catégorie (accessible sans connexion)
Route::get('/categorie/{id}/modules', [\App\Http\Controllers\Backend\CategoryController::class, 'showCategoryModules'])->name('frontend.category.modules');
// --- Pages légales et conformité ---
Route::view('/mentions-legales', 'frontend.contenu.mentions-legales')->name('mentions-legales');
Route::view('/conditions-utilisation', 'frontend.contenu.conditions-utilisation')->name('conditions-utilisation');
Route::view('/confidentialite', 'frontend.contenu.confidentialite')->name('confidentialite');
Route::view('/cookies', 'frontend.contenu.cookies')->name('cookies');


// Route pour le Hub de connexion (Version corrigée)
Route::get('/connexion-choix', function () {
    // Si le fichier est dans resources/views/frontend/contenu/
    return view('frontend.contenu.login-hub'); 
})->name('login.selection');

// Diffusion d'un média stocké sur le disque Laravel "public" (storage/app/public)
Route::get('/media/storage/{path}', function (string $path) {
    $path = ltrim($path, '/');
    if ($path === '' || str_contains($path, '..') || str_contains($path, "\0")) {
        abort(404);
    }

    $disk = Storage::disk('public');
    if (!$disk->exists($path)) {
        abort(404);
    }

    return response()->file($disk->path($path));
})->where('path', '.*')->name('media.storage');

// Tes routes existantes restent inchangées, elles seront ciblées par les boutons du Hub
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/stagiaire/connexion', [\App\Http\Controllers\UserController::class, 'showCodeLoginForm'])->name('stagiaire.code.form');


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
// Détail d'un module dans sa hiérarchie pédagogique (URL canonique)
Route::get('/categorie/{category}/modules/{module}', [\App\Http\Controllers\Frontend\MFormationsController::class, 'show'])
    ->name('frontend.modules.show');
// Ancienne URL conservée pour compatibilité + redirection SEO vers l'URL canonique
Route::get('/modules/{module}', [\App\Http\Controllers\Frontend\MFormationsController::class, 'showLegacy'])
    ->name('frontend.modules.show.legacy');
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
// ☁️ Jeu - Nuage de mot (participation)
// ----------------------------------------------------------
Route::get('/oneduc/mot', [WordCloudParticipationController::class, 'home'])->name('wordcloud.join');
Route::post('/oneduc/mot', [WordCloudParticipationController::class, 'resolveCode'])->name('wordcloud.resolve');
Route::get('/oneduc/mot/{code}', [WordCloudParticipationController::class, 'joinByCode'])->name('wordcloud.join.code');
Route::post('/oneduc/mot/{code}', [WordCloudParticipationController::class, 'submit'])
    ->middleware('throttle:30,1')
    ->name('wordcloud.submit');
Route::get('/oneduc/mot/{code}/data', [WordCloudParticipationController::class, 'liveData'])
    ->name('wordcloud.live.data');

// ----------------------------------------------------------
// 🎡 Roue aléatoire (participation)
// ----------------------------------------------------------
Route::get('/oneduc/roue/{code}',       [RoueAleatoireParticipationController::class, 'show'])->name('roue.join');
Route::get('/oneduc/roue/{code}/state', [RoueAleatoireParticipationController::class, 'state'])->name('roue.state');

// ----------------------------------------------------------
// ❓ Mur de questions (participation)
// ----------------------------------------------------------
Route::get('/oneduc/questions/{code}', [QuestionWallParticipationController::class, 'joinByCode'])
    ->name('questions.join.code');
Route::post('/oneduc/questions/{code}/questions', [QuestionWallParticipationController::class, 'submitQuestion'])
    ->middleware(['auth', 'throttle:30,1'])
    ->name('questions.submit');
Route::post('/oneduc/questions/{code}/questions/{question}/vote', [QuestionWallParticipationController::class, 'toggleVote'])
    ->middleware(['auth', 'throttle:60,1'])
    ->name('questions.vote.toggle');




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
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
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
// 🔵 Observateur
// ----------------------------------------------------------
require __DIR__.'/observateur.php';

// ----------------------------------------------------------
// 🟣 Stagiaire
// ----------------------------------------------------------
// Routes accessibles aux stagiaires
require __DIR__.'/stagiaire.php';

// routes/web.php
// ... après les autres imports de routes
require __DIR__.'/scorm.php';
// ----------------------------------------------------------
// Api
// ----------------------------------------------------------
// Endpoints API divers
require __DIR__.'/api.php';

Route::get('/admin/stagiaires/{id}/debug-progression', function ($id) {
    // On force l'ID en entier (ici ce sera 6)
    $userId = (int)$id;
    
    // On compte les lignes dans chaque table pour cet utilisateur
    $tables = [
        'quiz_attempts (Tentatives)' => DB::table('quiz_attempts')
            ->where('user_id', $userId)->count(),

        // CORRECTION : on utilise bien 'attempt_id' ici
        'quiz_questions (Réponses détaillées)' => DB::table('quiz_attempt_questions')
            ->join('quiz_attempts', 'quiz_attempt_questions.attempt_id', '=', 'quiz_attempts.id')
            ->where('quiz_attempts.user_id', $userId)->count(),

        'scorm_scores (Scores SCORM)' => DB::table('scorm_scores')
            ->where('user_id', $userId)->count(),
        
        'scorm_interactions (Détails SCORM)' => DB::table('scorm_interactions')
            ->where('user_id', $userId)->count(),
        
        'progressions (Statut manuel)' => DB::table('progressions')
            ->where('user_id', $userId)->count(),
        
        'videos (Suivi vidéo)' => DB::table('video_segment_trackings')
            ->where('user_id', $userId)->count(),
    ];

    return $tables;
});

// ----------------------------------------------------------
// Inscription formateur
// ----------------------------------------------------------
// Formulaire d'inscription dédié aux formateurs
Route::get('/inscription-formateur', function () {
    return view('frontend.formateur.reg_formateur');
})->name('formateur.inscription.form');
// Traitement de l'inscription formateur
Route::post('/inscription-formateur', [\App\Http\Controllers\Formateur\FormateurProfileController::class, 'register'])->name('formateur.inscription');

// Connexion via code d’accès (stagiaire)
// Connexion d'un stagiaire via un code d'accès fourni par le formateur
Route::get('/stagiaire/connexion-code', [\App\Http\Controllers\UserController::class, 'showCodeLoginForm'])->name('stagiaire.code.form.legacy');
Route::post('/stagiaire/connexion-code', [\App\Http\Controllers\UserController::class, 'loginByCode'])->name('stagiaire.code.login');

// Formulaire de contact
Route::middleware(['throttle:contact'])->group(function () {
    Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
});
// Affichage du formulaire de contact
Route::get('/contact', fn () => view('frontend.contenu.contact'))->name('contact.form');
Route::get('/contact', [ContactController::class, 'index'])->name('contact'); // GET pour la page

Route::post('/admin/stagiaires/{user}/reset-progression', [StagiaireController::class, 'resetProgression'])
    ->name('admin.stagiaires.reset');
// Auth Laravel (Jetstream/Breeze)
// Routes d'authentification fournies par Laravel Breeze/Jetstream
require __DIR__.'/auth.php';
