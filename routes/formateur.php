<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormateurController;
use App\Http\Controllers\Formateur\GroupeController;
use App\Http\Controllers\Formateur\ObjectiveController;
use App\Http\Controllers\Formateur\LessonResourceController;
use App\Http\Controllers\Formateur\ProgressionController;
use App\Http\Controllers\Formateur\LiveQuizSessionController;
use App\Http\Controllers\Formateur\WordCloudController as FormateurWordCloudController;
use App\Http\Controllers\Formateur\WhiteboardController;
use App\Http\Controllers\Backend\ModuleController;
// use App\Http\Controllers\Stagiaire\QuizAttemptController;
use App\Http\Controllers\Stagiaire\QuizController;


Route::middleware(['auth', 'role:formateur'])
    ->prefix('formateur')
    ->name('formateur.')
    ->group(function () {

    // 🖥️ Dashboard & profil formateur
    Route::get('/', [FormateurController::class, 'FormateurDashboard'])->name('dashboard');
    Route::get('/dashboard/activity', [FormateurController::class, 'dashboardActivity'])->name('dashboard.activity');
    Route::get('/profile', [FormateurController::class, 'FormateurProfile'])->name('profile');
    Route::get('/parametre', [FormateurController::class, 'FormateurParametre'])->name('parametre');
    Route::get('/documentation', fn () => view('formateur.documentation'))->name('documentation');
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

    // 🧑‍🤝‍🧑 Groupes
    Route::get('/groupes', [GroupeController::class, 'index'])->name('groupes.index');
    Route::get('/groupes/create', [GroupeController::class, 'create'])->name('groupes.create');
    Route::post('/groupes', [GroupeController::class, 'store'])->name('groupes.store');
    Route::get('/groupes/{id}/edit', [GroupeController::class, 'edit'])->name('groupes.edit');
    Route::put('/groupes/{id}', [GroupeController::class, 'update'])->name('groupes.update');
    Route::delete('/groupes/{id}', [GroupeController::class, 'destroy'])->name('groupes.destroy');
    Route::prefix('/groupes/{group}/tableau-blanc')
        ->name('groupes.whiteboard.')
        ->group(function () {
            Route::get('/', [WhiteboardController::class, 'show'])->name('show');
            Route::get('/snapshot', [WhiteboardController::class, 'snapshot'])->name('snapshot');
            Route::post('/items', [WhiteboardController::class, 'upsert'])->name('items.upsert');
            Route::delete('/items/{item}', [WhiteboardController::class, 'destroy'])->name('items.destroy');
            Route::post('/clear', [WhiteboardController::class, 'clear'])->name('clear');
        });

    // 📈 Progression des stagiaires
    Route::get('/progressions', function () {
        return redirect()->route('formateur.progressions.groupes');
    })->name('progressions.index');

    Route::get('/progressions/groupes', [ProgressionController::class, 'index'])
        ->name('progressions.groupes')
        ->defaults('view', 'groupes');

    Route::get('/progressions/stagiaires', [ProgressionController::class, 'index'])
        ->name('progressions.stagiaires')
        ->defaults('view', 'stagiaires');

    Route::get('/progressions/stagiaire/{user}', [ProgressionController::class, 'index'])
        ->name('progressions.stagiaire')
        ->defaults('view', 'stagiaire');
        
    Route::get('/progressions/modules', [ProgressionController::class, 'index'])
    ->name('progressions.modules')
    ->defaults('view', 'modules');


    // ✅ IMPORTANT : route AJAX/SCORM
    Route::post('/progression/complete', [ProgressionController::class, 'markCompleted'])
        ->name('progression.complete');

    // 📂 Formations
    Route::get('/formations', [FormateurController::class, 'mesModules'])->name('formations.index');
    Route::get('/formations/{module}/detail', [FormateurController::class, 'moduleDetail'])->name('formations.detail');
    Route::get('/formations/{module}/preview', [FormateurController::class, 'preview'])->name('formations.preview');
    Route::get('/objectifs/recherche', [ObjectiveController::class, 'index'])->name('objectifs.index');

    Route::get('/formations/{module}/section/{section}', [ModuleController::class, 'section'])
        ->name('formations.section');

    Route::get('/formations/{module}/section/{section}/lesson/{lecture}', [ModuleController::class, 'lire'])
        ->name('formations.lecture');

    Route::post('/formations/{module}/section/{section}/lesson/{lecture}/resources', [LessonResourceController::class, 'store'])
        ->name('formations.lesson.resources.store');

    Route::post('/formations/{module}/section/{section}/lesson/{lecture}/resources/{resource}/visibility', [LessonResourceController::class, 'toggleVisibility'])
        ->name('formations.lesson.resources.visibility');

    Route::delete('/formations/{module}/section/{section}/lesson/{lecture}/resources/{resource}', [LessonResourceController::class, 'destroy'])
        ->name('formations.lesson.resources.destroy');

    Route::prefix('/word-clouds')
        ->name('wordclouds.')
        ->group(function () {
            Route::post('/', [FormateurWordCloudController::class, 'store'])->name('store');
            Route::get('/{wordCloud}/live', [FormateurWordCloudController::class, 'live'])->name('live');
            Route::get('/{wordCloud}/live/data', [FormateurWordCloudController::class, 'liveData'])->name('live.data');
        });

       

    // Personnaliser les leçons d’un module pour un groupe
    Route::get('/groupes/{group}/modules/{module}/lecons', [GroupeController::class, 'editModuleLessons'])
        ->name('groupes.modules.lecons.edit');

    Route::post('/groupes/{group}/modules/{module}/lecons/{lecture}/toggle', [GroupeController::class, 'toggleModuleLesson'])
        ->name('groupes.modules.lecons.toggle');

    Route::post('/groupes/{group}/modules/{module}/lecons/{lecture}/move-up', [GroupeController::class, 'moveModuleLessonUp'])
        ->name('groupes.modules.lecons.move.up');

    Route::post('/groupes/{group}/modules/{module}/lecons/{lecture}/move-down', [GroupeController::class, 'moveModuleLessonDown'])
        ->name('groupes.modules.lecons.move.down');

    Route::post('/groupes/{group}/modules/{module}/lecons/reset', [GroupeController::class, 'resetModuleLessons'])
        ->name('groupes.modules.lecons.reset');

        // Dans le groupe middleware(['auth', 'role:formateur']) ...

// Route pour modifier le nombre de questions du quiz (Ajax ou Post classique)
Route::post('/formations/lecture/{lecture}/update-quiz-count', [FormateurController::class, 'updateQuizCount'])
        ->name('lecture.update_quiz_count');

  /*
|--------------------------------------------------------------------------
| Quiz DANS la leçon (mêmes écrans que stagiaire)
|--------------------------------------------------------------------------
*/
Route::get('/formations/{module}/section/{section}/lesson/{lecture}/quiz/start', [QuizController::class, 'start'])
    ->name('quiz.start')
    ->middleware('signed');

Route::prefix('/formations/{module}/section/{section}/lesson/{lecture}/quiz/{attempt}')
    ->name('lesson.quiz.')
    ->group(function () {
        Route::get('/question', [QuizController::class, 'showQuestion'])->name('question');
        Route::post('/answer', [QuizController::class, 'answer'])->name('answer');
        Route::get('/result', [QuizController::class, 'result'])->name('result');
        Route::post('/restart', [QuizController::class, 'restart'])->name('restart');
    });

Route::prefix('/formations/{module}/section/{section}/lesson/{lecture}/live-quiz')
    ->name('live-quiz.')
    ->group(function () {
        Route::post('/', [LiveQuizSessionController::class, 'store'])->name('store');
        Route::get('/sessions/{session}', [LiveQuizSessionController::class, 'show'])->name('show');
        Route::post('/sessions/{session}/start', [LiveQuizSessionController::class, 'start'])->name('start');
        Route::post('/sessions/{session}/reveal', [LiveQuizSessionController::class, 'reveal'])->name('reveal');
        Route::post('/sessions/{session}/next', [LiveQuizSessionController::class, 'next'])->name('next');
        Route::post('/sessions/{session}/close', [LiveQuizSessionController::class, 'close'])->name('close');
        Route::get('/sessions/{session}/snapshot', [LiveQuizSessionController::class, 'snapshot'])->name('snapshot');
    });

Route::post('/live-quiz/launch', [LiveQuizSessionController::class, 'launch'])
    ->name('live-quiz.launch');



});
