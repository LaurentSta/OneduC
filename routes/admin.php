<?php
// routes/admin.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\GroupeController;
use App\Http\Controllers\Backend\ModuleController;
use App\Http\Controllers\LessonFeedbackController;
use App\Http\Controllers\Backend\EvaluationController;
use App\Http\Controllers\Backend\SkillReferentialController;
use App\Http\Controllers\Backend\SkillDomainController;
use App\Http\Controllers\Backend\SkillController;
use App\Http\Controllers\Backend\QuizQuestionController;
use App\Http\Controllers\Backend\ScormLibraryController;


Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', [AdminController::class, 'AdminDashboard'])->name('dashboard');
        Route::get('/profile', [AdminController::class, 'AdminProfile'])->name('profile');

        Route::get('/formateurs', [AdminController::class, 'AllFormateur'])->name('formateurs');
        Route::delete('/formateurs/{user}', [AdminController::class, 'DestroyFormateur'])->name('formateurs.destroy');
        Route::post('/update-user-status', [AdminController::class, 'UpdateUserStatus'])->name('update.user.status');

        Route::get('/stagiaires', [AdminController::class, 'AllStagiaires'])->name('stagiaires.index');
        Route::delete('/stagiaires/{user}', [AdminController::class, 'DestroyStagiaire'])->name('stagiaires.destroy');

        // Catégories
        Route::controller(CategoryController::class)->group(function () {
            Route::get('/categories', 'AllCategory')->name('categories.all');
            Route::get('/categories/ajout', 'AddCategory')->name('categories.ajout');
            Route::post('/categories/store', 'StoreCategory')->name('categories.store');
            Route::get('/categories/edit/{id}', 'EditCategory')->name('categories.edit');
            Route::post('/categories/update', 'UpdateCategory')->name('categories.update');
            // TODO: idéalement passer en DELETE
            Route::get('/categories/delete/{id}', 'DeleteCategory')->name('categories.delete');

            Route::get('/sous-categories', 'AllSubCategory')->name('subcategories.all');
            Route::get('/sous-categories/ajout', 'AddSubCategory')->name('subcategories.add');
            Route::post('/sous-categories/store', 'StoreSubCategory')->name('subcategories.store');
            Route::get('/sous-categories/edit/{id}', 'EditSubCategory')->name('subcategories.edit');
            Route::post('/sous-categories/update', 'UpdateSubCategory')->name('subcategories.update');
            // TODO: idéalement passer en DELETE
            Route::get('/sous-categories/delete/{id}', 'DeleteSubCategory')->name('subcategories.delete');
        });

        // Groupes
        Route::controller(GroupeController::class)->group(function () {
            Route::get('/groupes', 'AllGroupe')->name('groupes');
            Route::get('/groupes/ajout', 'AddGroupe')->name('groupes.add');
            Route::post('/groupes', 'StoreGroupe')->name('groupes.store');
            Route::get('/groupes/{id}/edit', 'EditGroupe')->name('groupes.edit');
            Route::put('/groupes/{id}', 'UpdateGroupe')->name('groupes.update');
            Route::delete('/groupes/{id}', 'DeleteGroupe')->name('groupes.delete');
        });

        // Modules
        Route::controller(ModuleController::class)->group(function () {
            Route::get('/modules', 'Modules')->name('modules');
            Route::get('/modules/ajout', 'AddModule')->name('modules.add');
            Route::post('/modules/store', 'StoreModule')->name('modules.store');
            Route::get('/modules/edit/{id}', 'EditModule')->name('modules.edit');
            Route::put('/modules/update/{id}', 'UpdateModule')->name('modules.update');
            // TODO: idéalement passer en DELETE
            Route::get('/modules/delete/{id}', 'DeleteModule')->name('modules.delete');

            Route::patch('/modules/{module}/toggle-status', 'toggleStatus')->name('modules.toggle-status');

            Route::get('/modules/{id}/lectures/add', 'AddModuleLecture')->name('modules.lecture.add');
            Route::post('/modules/sections/store', 'AddModuleSection')->name('modules.section.store');
            Route::post('/modules/lectures/store', 'SaveLecture')->name('modules.lecture.store');

            Route::get('/lectures/edit/{id}', 'EditLecture')->name('lectures.edit');
            Route::post('/lectures/update', 'UpdateModuleLecture')->name('lectures.update');
            // TODO: idéalement passer en DELETE
            Route::get('/lectures/delete/{id}', 'DeleteLecture')->name('lectures.delete');

            Route::post('/sections/delete/{id}', 'DeleteSection')->name('sections.delete');
            Route::get('/sections/edit/{id}', 'EditModuleSection')->name('sections.edit');
            Route::post('/sections/update/{id}', 'UpdateModuleSection')->name('sections.update');

            Route::get('/lectures/{id}/move-up', 'MoveLectureUp')->name('lectures.move.up');
            Route::get('/lectures/{id}/move-down', 'MoveLectureDown')->name('lectures.move.down');
        });

        // Evaluations
        Route::controller(EvaluationController::class)
            ->prefix('evaluations')
            ->name('evaluations.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');

                Route::get('/{evaluation}', 'show')->name('show');
                Route::get('/{evaluation}/edit', 'edit')->name('edit');
                Route::put('/{evaluation}', 'update')->name('update');
                Route::delete('/{evaluation}', 'destroy')->name('destroy');
                
            });

        // Référentiels
        Route::resource('referentiels', SkillReferentialController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->names('referentiels');

        Route::prefix('referentiels/{referentiel}')->group(function () {

            Route::resource('domaines', SkillDomainController::class)
                ->parameters(['domaines' => 'domain'])
                ->except(['show'])
                ->names('referentiels.domains');

            Route::resource('competences', SkillController::class)
                ->parameters(['competences' => 'skill'])
                ->except(['show'])
                ->names('referentiels.skills');
        });

        // Quiz (banque de questions)
        Route::prefix('lectures/{lecture}/quiz')->name('quiz.')->group(function () {
            Route::get('/questions', [QuizQuestionController::class, 'index'])->name('questions.index');
            Route::get('/questions/create', [QuizQuestionController::class, 'create'])->name('questions.create');
            Route::post('/questions', [QuizQuestionController::class, 'store'])->name('questions.store');

            Route::get('/questions/{question}/edit', [QuizQuestionController::class, 'edit'])->name('questions.edit');
            Route::put('/questions/{question}', [QuizQuestionController::class, 'update'])->name('questions.update');
            Route::delete('/questions/{question}', [QuizQuestionController::class, 'destroy'])->name('questions.destroy');
        });

        // Retours
        Route::get('/retours', [LessonFeedbackController::class, 'adminIndex'])
            ->name('retours.index');

        Route::delete('/retours/{id}', [LessonFeedbackController::class, 'destroy'])
            ->name('retours.delete');


        /*
    |--------------------------------------------------------------------------
    | SCORM – Import (utilisé depuis l’édition de leçon)
    |--------------------------------------------------------------------------
    */
    // routes/admin.php
Route::prefix('scorm')->name('scorm.')->group(function () {
    Route::post('/import-lecture', [ScormLibraryController::class, 'importForLecture'])->name('import');
});


        


    });
