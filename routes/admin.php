<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\GroupeController;
use App\Http\Controllers\Backend\ModuleController;
use App\Http\Controllers\LessonFeedbackController;
use App\Http\Controllers\Backend\EvaluationController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // 🖥️ Dashboard
    Route::get('/', [AdminController::class, 'AdminDashboard'])->name('dashboard');

    // 👤 Profil admin
    Route::get('/profile', [AdminController::class, 'AdminProfile'])->name('profile');


    // 👤 Formateurs
    Route::get('/formateurs', [AdminController::class, 'AllFormateur'])->name('formateurs');
    Route::post('/update-user-status', [AdminController::class, 'UpdateUserStatus'])->name('update.user.status');
    // 👤 Stagiaires
    Route::get('/stagiaires', [AdminController::class, 'AllStagiaires'])->name('stagiaires.index');


    // 📁 Catégories
    Route::controller(CategoryController::class)->group(function () {
        Route::get('/categories', 'AllCategory')->name('categories.all');
        Route::get('/categories/ajout', 'AddCategory')->name('categories.ajout');
        Route::post('/categories/store', 'StoreCategory')->name('categories.store');
        Route::get('/categories/edit/{id}', 'EditCategory')->name('categories.edit');
        Route::post('/categories/update', 'UpdateCategory')->name('categories.update');
        Route::get('/categories/delete/{id}', 'DeleteCategory')->name('categories.delete');

        Route::get('/sous-categories', 'AllSubCategory')->name('subcategories.all');
        Route::get('/sous-categories/ajout', 'AddSubCategory')->name('subcategories.add');
        Route::post('/sous-categories/store', 'StoreSubCategory')->name('subcategories.store');
        Route::get('/sous-categories/edit/{id}', 'EditSubCategory')->name('subcategories.edit');
        Route::post('/sous-categories/update', 'UpdateSubCategory')->name('subcategories.update');
        Route::get('/sous-categories/delete/{id}', 'DeleteSubCategory')->name('subcategories.delete');
    });

    // 👥 Groupes
    Route::controller(GroupeController::class)->group(function () {
        Route::get('/groupes', 'AllGroupe')->name('groupes');
        Route::get('/groupes/ajout', 'AddGroupe')->name('groupes.add');
        Route::post('/groupes', 'StoreGroupe')->name('groupes.store');
        Route::get('/groupes/{id}/edit', 'EditGroupe')->name('groupes.edit');
        Route::put('/groupes/{id}', 'UpdateGroupe')->name('groupes.update');
        Route::delete('/groupes/{id}', 'DeleteGroupe')->name('groupes.delete');
    });

    // 📦 Modules
    Route::controller(ModuleController::class)->group(function () {
        Route::get('/modules', 'Modules')->name('modules');
        Route::get('/modules/ajout', 'AddModule')->name('modules.add');
        Route::post('/modules/store', 'StoreModule')->name('modules.store');
        Route::get('/modules/edit/{id}', 'EditModule')->name('modules.edit');
        Route::put('/modules/update/{id}', 'UpdateModule')->name('modules.update');
        Route::get('/modules/delete/{id}', 'DeleteModule')->name('modules.delete');

        // 🎬 Lectures & sections
        Route::get('/modules/{id}/lectures/add', 'AddModuleLecture')->name('modules.lecture.add');
        Route::post('/modules/sections/store', 'AddModuleSection')->name('modules.section.store');
        Route::post('/modules/lectures/store', 'SaveLecture')->name('modules.lecture.store');
        Route::get('/lectures/edit/{id}', 'EditLecture')->name('lectures.edit');
        Route::post('/lectures/update', 'UpdateModuleLecture')->name('lectures.update');
        Route::get('/lectures/delete/{id}', 'DeleteLecture')->name('lectures.delete');

        Route::post('/sections/delete/{id}', 'DeleteSection')->name('sections.delete');
        Route::get('/sections/edit/{id}', 'EditModuleSection')->name('sections.edit');
        Route::post('/sections/update/{id}', 'UpdateModuleSection')->name('sections.update');
        Route::get('/lectures/{id}/move-up', 'MoveLectureUp')->name('lectures.move.up');
        Route::get('/lectures/{id}/move-down', 'MoveLectureDown')->name('lectures.move.down');
        Route::get('/module/{id}/section/{section_id}', [ModuleController::class, 'section'])->name('module.section');

    });
        Route::controller(EvaluationController::class)->prefix('evaluations')->name('evaluations.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::get('/delete/{id}', 'destroy')->name('delete');
    });


    // 💬 Commentaires / retours
    Route::get('/retours', [LessonFeedbackController::class, 'adminIndex'])->name('retours.index');
    Route::delete('/retours/{id}', [LessonFeedbackController::class, 'destroy'])->name('retours.delete');
});
