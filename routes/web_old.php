<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FormateurController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\GroupeController;
use App\Http\Controllers\Backend\ModuleController;
use App\Http\Controllers\Frontend\MFormationsController;
use App\Http\Controllers\Frontend\LectureController;
use App\Http\Controllers\SCORMController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use \App\Http\Controllers\Formateur\ProgressionController;
use \App\Http\Controllers\ScormInteractionController;
use \App\Http\Controllers\LessonFeedbackController;


// ----------------------------------------------------------
// 🌐 Pages publiques générales
// ----------------------------------------------------------
Route::get('/',                           [UserController::class, 'Index'])->name('index');
Route::get('/le-projet-oneduc-fr',        [UserController::class, 'Projet'])->name('projet');
Route::get('/association',                [UserController::class, 'Association'])->name('association');
Route::get('/adhesion',                   [UserController::class, 'Adhesion'])->name('adhesion');
// ----------------------------------------------------------
// 🧠 Catégories & sous-catégories
// ----------------------------------------------------------
Route::get('/categories',                        [CategoryController::class, 'FrontCategories'])->name('frontend.categories');
Route::get('/categories/{id}/subcategories',     [CategoryController::class, 'showSubCategories'])->name('frontend.subcategories');
Route::get('/categories/{id}/modules',           [CategoryController::class, 'showCategoryModules'])->name('frontend.category.modules');
// ----------------------------------------------------------
// 📚 Modules de formation (MFormationsController)
// ----------------------------------------------------------
Route::get('/MFormations',               [MFormationsController::class, 'index'])->name('frontend.modules.index');
Route::get('/modules/{id}',              [MFormationsController::class, 'show'])->name('frontend.modules.show');
// ----------------------------------------------------------
// 🎥 Lecture d’une leçon (LectureController)
// ----------------------------------------------------------
Route::get('/lecture/{id}',              [LectureController::class, 'show'])->name('lecture.show');
Route::get('/formation/module/{id}/lecture', [ModuleController::class, 'lire'])->name('module.lecture');

// ----------------------------------------------------------
// 🔐 Authentification
// ----------------------------------------------------------
// Connexion
Route::get('/connexion',                 [AuthenticatedSessionController::class, 'create'])->name('connexion');
Route::post('/connexion',                [AuthenticatedSessionController::class, 'store'])->name('login.process');
// Inscription
// Route::get('/inscription',              [UserController::class, 'Register'])->name('inscription');
// Déconnexion
Route::post('/logout',                  [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::post('/lecture/{id}/valider', [LectureController::class, 'valider'])->name('lecture.valider');
});
Route::get('/inscription',              [UserController::class, 'Register'])->name('inscription');

// 🟣 ROUTES STAGIAIRE - accessible après connexion
Route::middleware(['auth', 'role:stagiaire', 'track.time'])->group(function () {

    Route::get('/stagiaire/dashboard', [UserController::class, 'StagiaireDashboard'])->name('stagiaire.dashboard');
    Route::get('/stagiaire/profile', [UserController::class, 'UserProfile'])->name('stagiaire.profile');
    Route::get('/stagiaire/parametre', [UserController::class, 'UserParametre'])->name('stagiaire.parametre');
    Route::post('/stagiaire/profil/store', [UserController::class, 'UserProfilStore'])->name('stagiaire.profil.store');
    Route::get('/stagiaire/securite', [UserController::class, 'showUserSecurite'])->name('stagiaire.securite.show');
    Route::get('/stagiaire/modules', [UserController::class, 'StagiaireModules'])->name('stagiaire.modules');
    Route::get('/stagiaire/resultats', [UserController::class, 'StagiaireResultats'])->name('stagiaire.resultats');
});


// Gestion du profil utilisateur (Laravel Breeze / Jetstream standard)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
// 🟢 ROUTES ADMINISTRATEUR
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'AdminDashboard'])->name('admin.dashboard');
    Route::get('/admin/profile', [AdminController::class, 'AdminProfile'])->name('admin.profile');
    Route::get('/admin/parametre', [AdminController::class, 'AdminParametre'])->name('admin.parametre');
    Route::post('/admin/profil/store', [AdminController::class, 'AdminProfilStore'])->name('admin.profil.store');
    Route::get('/admin/securite', [AdminController::class, 'showAdminSecurite'])->name('admin.securite.show');
    Route::post('/admin/securite', [AdminController::class, 'AdminSecurite'])->name('admin.securite');

    // Gestion des catégories
    Route::controller(CategoryController::class)->group(function () {
        Route::get('/all/category', 'AllCategory')->name('all.category');
        Route::get('/add/category', 'AddCategory')->name('add.category');
        Route::post('/store/category', 'StoreCategory')->name('store.category');
        Route::get('/edit/category/{id}', 'EditCategory')->name('edit.category');
        Route::post('/update/category', 'UpdateCategory')->name('update.category');
        Route::get('/delete/category/{id}', 'DeleteCategory')->name('delete.category');
    });
    // Gestion des sous-catégories
    Route::controller(CategoryController::class)->group(function () {
        Route::get('/all/subcategory', 'AllSubCategory')->name('all.subcategory');
        Route::get('/add/subcategory', 'AddSubCategory')->name('add.subcategory');
        Route::post('/store/subcategory', 'StoreSubCategory')->name('store.subcategory');
        Route::get('/edit/subcategory/{id}', 'EditSubCategory')->name('edit.subcategory');
        Route::post('/update/subcategory', 'UpdateSubCategory')->name('update.subcategory');
        Route::get('/delete/subcategory/{id}', 'DeleteSubCategory')->name('delete.subcategory');
    });
    // Gestion des formateurs
    Route::controller(AdminController::class)->group(function () {
        Route::get('/all/formateur', 'AllFormateur')->name('all.formateur');
        Route::post('/update/user/status', 'UpdateUserStatus')->name('update.user.status');
    });
    // Gestion des groupes
    Route::controller(GroupeController::class)->group(function () {
        Route::get('/groupes', 'AllGroupe')->name('groupes'); // ✅ Correction ici
        Route::get('/groupe/add', 'AddGroupe')->name('add.groupe');
        Route::post('/groupe', 'StoreGroupe')->name('store.groupe');
        Route::get('/groupe/{id}/edit', 'EditGroupe')->name('edit.groupe');
        Route::put('/groupe/{id}', 'UpdateGroupe')->name('update.groupe');
        Route::delete('/groupe/{id}', 'DeleteGroupe')->name('delete.groupe');
    });
    // Gestion des Modules
    Route::controller(ModuleController::class)->group(function () {
        Route::get('/admin/modules', 'Modules')->name('modules');
        Route::get('/ajout/modules', 'AddModule')->name('add.module');
        Route::post('/store/module', 'StoreModule')->name('store.module');
        Route::get('/module/edit/{id}', 'EditModule')->name('edit.module');
        Route::post('/module/update/{id}', 'UpdateModule')->name('update.module');
        Route::get('/module/delete/{id}', 'DeleteModule')->name('delete.module');
    });

    // Module Section and Lecture All Route
    Route::controller(ModuleController::class)->group(function(){
        Route::get('/ajout/module/lecture/{id}','AddModuleLecture')->name('add.module.lecture');
        Route::post('/add/module/section/','AddModuleSection')->name('add.module.section');
        Route::post('/save-lecture/','SaveLecture')->name('save-lecture');
        Route::get('/edit/lecture/{id}','EditLecture')->name('edit.lecture');
        Route::post('/update/module/lecture','UpdateModuleLecture')->name('update.module.lecture');
        Route::get('/delete/lecture/{id}','DeleteLecture')->name('delete.lecture');
        Route::post('/delete/section/{id}','DeleteSection')->name('delete.section');
        Route::get('/edit/section/{id}', 'EditModuleSection')->name('edit.module.section');
        Route::post('/update/section/{id}', 'UpdateModuleSection')->name('update.module.section');
        Route::get('/lecture/{id}/move-up', [ModuleController::class, 'MoveLectureUp'])->name('lecture.move.up');
        Route::get('/lecture/{id}/move-down', [ModuleController::class, 'MoveLectureDown'])->name('lecture.move.down');

    // Gestion Commentaire
    Route::controller(LessonFeedbackController::class)->group(function () {
        Route::get('/admin/retours', 'adminIndex')->name('admin.retours.index');
    });

    Route::delete('/admin/retours/{id}', [LessonFeedbackController::class, 'destroy'])->name('admin.retours.delete');




    });

});

Route::middleware(['auth'])->group(function () {
    Route::post('/feedback', [LessonFeedbackController::class, 'store'])->name('feedback.store');
    Route::get('/feedback/{lesson}', [LessonFeedbackController::class, 'index'])->name('feedback.index');
});



// 🟠 ROUTES FORMATEUR
Route::middleware(['auth', 'role:formateur'])->group(function () {

    Route::get('/formateur/dashboard', [FormateurController::class, 'FormateurDashboard'])->name('formateur.dashboard');
    Route::get('/formateur/profile', [FormateurController::class, 'FormateurProfile'])->name('formateur.profile');
    Route::get('/formateur/parametre', [FormateurController::class, 'FormateurParametre'])->name('formateur.parametre');
    Route::post('/formateur/profil/store', [FormateurController::class, 'FormateurProfilStore'])->name('formateur.profil.store');
    Route::get('/formateur/securite', [FormateurController::class, 'showFormateurSecurite'])->name('formateur.securite.show');
    Route::post('/formateur/securite', [FormateurController::class, 'FormateurSecurite'])->name('formateur.securite');

    // Nouvelle gestion des stagiaires
    Route::get('/formateur/stagiaires', [FormateurController::class, 'indexStagiaires'])->name('formateur.stagiaires.index');
    Route::get('/formateur/stagiaires/create', [FormateurController::class, 'createStagiaire'])->name('formateur.stagiaires.create');
    Route::post('/formateur/stagiaires', [FormateurController::class, 'storeStagiaire'])->name('formateur.stagiaires.store');
    Route::get('/formateur/stagiaires/{id}/edit', [FormateurController::class, 'editStagiaire'])->name('formateur.stagiaires.edit');
    Route::put('/formateur/stagiaires/{id}', [FormateurController::class, 'updateStagiaire'])->name('formateur.stagiaires.update');
    Route::delete('/formateur/stagiaires/{id}', [FormateurController::class, 'destroyStagiaire'])->name('formateur.stagiaires.destroy');


    // ➕ Création de groupe via wizard
    Route::get('/formateur/groupes/create', [\App\Http\Controllers\Formateur\GroupeController::class, 'create'])->name('groupes.formateur.create');
    Route::post('/formateur/groupes', [\App\Http\Controllers\Formateur\GroupeController::class, 'store'])->name('groupes.formateur.store');
    Route::get('/formateur/groupes/{id}/edit', [\App\Http\Controllers\Formateur\GroupeController::class, 'edit'])->name('groupes.formateur.edit');
    Route::put('/formateur/groupes/{id}', [\App\Http\Controllers\Formateur\GroupeController::class, 'update'])->name('groupes.formateur.update');


    // 🗂️ Affichage des groupes du formateur
    Route::get('/formateur/groupes', [\App\Http\Controllers\Formateur\GroupeController::class, 'index'])->name('formateur.groupes.index');

    Route::get('/formateur/progressions', [ProgressionController::class, 'index'])->name('formateur.progressions.index');
    Route::post('/progression/complete', [ProgressionController::class, 'markCompleted'])
    ->middleware('auth')
    ->name('progression.complete');



});
// Plusieurs rôles d'un coup
Route::middleware(['auth', 'role:admin,formateur'])->group(function () {
    // routes partagées
});
Route::post('/scorm/save-progress', [SCORMController::class, 'saveProgress'])
    ->withoutMiddleware([VerifyCsrfToken::class]);
Route::get('/lecture/{id}/scorm', [LectureController::class, 'showScorm'])->name('lecture.scorm');
Route::post('/scorm/progress', [\App\Http\Controllers\SCORMController::class, 'saveProgress'])->middleware('auth');
// GET = afficher le formulaire
Route::get('/inscription-formateur', function () {
    return view('frontend.formateur.reg_formateur');
})->name('formateur.inscription.form'); // 🟢 nom différent
// POST = traitement du formulaire
Route::post('/inscription-formateur', [App\Http\Controllers\FormateurController::class, 'register'])->name('formateur.inscription'); // 🟢 OK

// Authentification Laravel (Breeze / Jetstream)
require __DIR__.'/auth.php';
