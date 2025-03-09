<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\GroupeController; // ✅ Ajout de l'import manquant

// Routes publiques
Route::get('/', [UserController::class, 'Index'])->name('index');
Route::get('/le-projet-oneduc-fr', [UserController::class, 'Projet'])->name('projet');
Route::get('/association', [UserController::class, 'Association'])->name('association');
Route::get('/adhesion', [UserController::class, 'Adhesion'])->name('adhesion');
Route::get('/connexion', [AdminController::class, 'Login'])->name('connexion');
Route::get('/inscription', [UserController::class, 'Register'])->name('inscription');
Route::get('/user/logout', [UserController::class, 'UserLogout'])->name('user.logout');

// Sécurisation du profil utilisateur
Route::middleware('auth')->group(function () {
    Route::get('/user/profile', [UserController::class, 'UserProfile'])->name('user.profile');
    Route::get('/user/parametre', [UserController::class, 'UserParametre'])->name('user.parametre');
    Route::post('/user/profil/store', [UserController::class, 'UserProfilStore'])->name('user.profil.store');
    Route::get('/user/securite', [UserController::class, 'showUserSecurite'])->name('user.securite.show');
});

// Tableau de bord utilisateur
Route::get('/dashboard', function () {
    return view('frontend.dashboard.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Gestion du profil utilisateur
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 🟢 ROUTES ADMINISTRATEUR
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'AdminDashboard'])->name('admin.dashboard');
    Route::get('/admin/logout', [AdminController::class, 'AdminLogout'])->name('admin.logout');
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

    // Gestion des instructeurs
    Route::controller(AdminController::class)->group(function () {
        Route::get('/all/instructor', 'AllInstructor')->name('all.instructor');
        Route::post('/update/user/status', 'UpdateUserStatus')->name('update.user.status');
    });

    // 🟢 Gestion des groupes par l'administrateur
    Route::controller(GroupeController::class)->group(function () {
        Route::get('/admin/groupes', 'index')->name('admin.groupes.index'); // Voir tous les groupes
        Route::get('/admin/groupes/create', 'create')->name('admin.groupes.create'); // Formulaire création
        Route::post('/admin/groupes', 'store')->name('admin.groupes.store'); // Enregistrer
        Route::get('/admin/groupes/{id}/edit', 'edit')->name('admin.groupes.edit'); // Modifier
        Route::put('/admin/groupes/{id}', 'update')->name('admin.groupes.update'); // Mettre à jour
        Route::delete('/admin/groupes/{id}', 'destroy')->name('admin.groupes.destroy'); // Supprimer
        Route::get('/admin/groupes/{id}/stagiaires', [GroupeController::class, 'assignStagiaires'])->name('admin.groupes.stagiaires');
        Route::post('/admin/groupes/{id}/stagiaires', [GroupeController::class, 'storeStagiaires'])->name('admin.groupes.stagiaires.store');

    });
}); // End Admin Group Middleware

// Formulaire inscription instructeur
Route::get('/become/instructor', [AdminController::class, 'BecomeInstructor'])->name('become.instructor');
Route::post('/instructor/register', [AdminController::class, 'InstructorRegister'])->name('instructor.register');

// 🟠 ROUTES INSTRUCTEUR
Route::middleware(['auth', 'role:instructor'])->group(function () {
    Route::get('/instructor/dashboard', [InstructorController::class, 'InstructorDashboard'])->name('instructor.dashboard');
    Route::get('/instructor/logout', [InstructorController::class, 'InstructorLogout'])->name('instructor.logout');
    Route::get('/instructor/profile', [InstructorController::class, 'InstructorProfile'])->name('instructor.profile');
    Route::get('/instructor/parametre', [InstructorController::class, 'InstructorParametre'])->name('instructor.parametre');
    Route::post('/instructor/profil/store', [InstructorController::class, 'InstructorProfilStore'])->name('instructor.profil.store');
    Route::get('/instructor/securite', [InstructorController::class, 'showInstructorSecurite'])->name('instructor.securite.show');

    // 🟠 Gestion des groupes par l'instructeur (restrictions possibles)
    Route::controller(GroupeController::class)->group(function () {
        Route::get('/instructor/groupes', 'index')->name('instructor.groupes.index'); // Voir ses groupes
        Route::get('/instructor/groupes/create', 'create')->name('instructor.groupes.create'); // Formulaire création
        Route::post('/instructor/groupes', 'store')->name('instructor.groupes.store'); // Enregistrer
        Route::get('/instructor/groupes/{id}/edit', 'edit')->name('instructor.groupes.edit'); // Modifier
        Route::put('/instructor/groupes/{id}', 'update')->name('instructor.groupes.update'); // Mettre à jour
    });
});

// Authentification Laravel
require __DIR__.'/auth.php';
