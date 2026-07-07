<?php

use App\Http\Controllers\Backend\ModuleController;
use App\Http\Controllers\Formateur\EmargementController;
use App\Http\Controllers\Formateur\FormateurModuleController;
use App\Http\Controllers\Formateur\FormateurProfileController;
use App\Http\Controllers\Formateur\FormateurStagiaireController;
use App\Http\Controllers\Formateur\GroupeController;
use App\Http\Controllers\Formateur\GroupeModuleLessonController;
use App\Http\Controllers\Formateur\GroupeWordCloudController;
use App\Http\Controllers\Formateur\LessonResourceController;
use App\Http\Controllers\Formateur\LiveQuizSessionController;
use App\Http\Controllers\Formateur\MesFormationsController;
use App\Http\Controllers\Formateur\ModuleBuilderController;
use App\Http\Controllers\Formateur\ObjectiveController;
use App\Http\Controllers\Formateur\OnboardingController;
use App\Http\Controllers\Formateur\OutilsEchelleController;
use App\Http\Controllers\Formateur\OutilsLiveQuizController;
use App\Http\Controllers\Formateur\OutilsNumeriquesController;
use App\Http\Controllers\Formateur\OutilsPagesCollaborativesController;
use App\Http\Controllers\Formateur\OutilsSondageController;
use App\Http\Controllers\Formateur\ParcoursController;
use App\Http\Controllers\Formateur\ProgressionController;
use App\Http\Controllers\Formateur\ProgressionGroupesController;
use App\Http\Controllers\Formateur\ProgressionModulesController;
use App\Http\Controllers\Formateur\ProgressionStagiaireController;
use App\Http\Controllers\Formateur\ProgressionStagiairesController;
use App\Http\Controllers\Formateur\QuestionWallController;
use App\Http\Controllers\Formateur\RoueAleatoireController;
use App\Http\Controllers\Formateur\WhiteboardController;
use App\Http\Controllers\Formateur\WordCloudController as FormateurWordCloudController;
use App\Http\Controllers\FormateurController;
use App\Http\Controllers\Stagiaire\QuizController;
// use App\Http\Controllers\Stagiaire\QuizAttemptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:formateur', 'association.member'])
    ->prefix('formateur')
    ->name('formateur.')
    ->group(function () {

        // 🖥️ Dashboard & profil formateur
        Route::get('/', [FormateurController::class, 'FormateurDashboard'])->name('dashboard');
        Route::get('/dashboard/activity', [FormateurController::class, 'dashboardActivity'])->name('dashboard.activity');
        Route::get('/parcours/{step?}', [OnboardingController::class, 'show'])->name('onboarding.show');
        Route::prefix('/parcours-formateur')
            ->name('parcours.')
            ->group(function () {
                Route::get('/', [ParcoursController::class, 'index'])->name('index');
                Route::post('/modules/{module}/questionnaire', [ParcoursController::class, 'submitModuleQuestionnaire'])
                    ->middleware('throttle:10,1')
                    ->name('questionnaire.submit');
                Route::get('/modules/{module}', [ParcoursController::class, 'showModule'])->name('modules.show');
                Route::get('/modules/{module}/introduction', [ParcoursController::class, 'showModuleIntroduction'])->name('modules.introduction');
                Route::get('/modules/{module}/chapitres/{chapter}', [ParcoursController::class, 'showChapter'])->name('chapters.show');
                Route::get('/modules/{module}/chapitres/{chapter}/lecons/{lesson}/parties/{part}', [ParcoursController::class, 'showLessonPart'])->name('lessons.part');
                Route::post('/modules/{module}/chapitres/{chapter}/lecons/{lesson}/parties/{part}/validation', [ParcoursController::class, 'completeGuidedLessonPart'])->name('lessons.part.complete');
                Route::get('/modules/{module}/chapitres/{chapter}/lecons/{lesson}', [ParcoursController::class, 'showLesson'])->name('lessons.show');
                Route::get('/modules/{module}/chapitres/{chapter}/lecons/{lesson}/activites/{activity}', [ParcoursController::class, 'showActivity'])->name('activities.show');
                Route::post('/modules/{module}/chapitres/{chapter}/lecons/{lesson}/activites/{activity}', [ParcoursController::class, 'submitActivity'])->name('activities.submit');
            });
        // Profil & sécurité
        Route::get('/profile', [FormateurProfileController::class, 'FormateurProfile'])->name('profile');
        Route::get('/parametre', [FormateurProfileController::class, 'FormateurParametre'])->name('parametre');
        Route::get('/documentation', fn () => view('formateur.documentation'))->name('documentation');
        Route::post('/profil/store', [FormateurProfileController::class, 'FormateurProfilStore'])->name('profil.store');
        Route::get('/securite', [FormateurProfileController::class, 'showFormateurSecurite'])->name('securite.show');
        Route::post('/securite', [FormateurProfileController::class, 'FormateurSecurite'])->name('securite');
        Route::delete('/compte', [FormateurProfileController::class, 'destroyOwnAccount'])->name('account.destroy');

        // 👤 Stagiaires
        Route::get('/stagiaires', [FormateurStagiaireController::class, 'indexStagiaires'])->name('stagiaires.index');
        Route::get('/stagiaires/create', [FormateurStagiaireController::class, 'createStagiaire'])->name('stagiaires.create');
        Route::post('/stagiaires', [FormateurStagiaireController::class, 'storeStagiaire'])->name('stagiaires.store');
        Route::get('/stagiaires/{id}/edit', [FormateurStagiaireController::class, 'editStagiaire'])->name('stagiaires.edit');
        Route::put('/stagiaires/{id}', [FormateurStagiaireController::class, 'updateStagiaire'])->name('stagiaires.update');
        Route::delete('/stagiaires/{id}', [FormateurStagiaireController::class, 'destroyStagiaire'])->name('stagiaires.destroy');
        Route::post('/stagiaires/{id}/message', [FormateurStagiaireController::class, 'sendMessage'])->name('stagiaires.message.send');

        // 🧑‍🤝‍🧑 Groupes
        Route::get('/groupes', [GroupeController::class, 'index'])->name('groupes.index');
        Route::get('/groupes/create', [GroupeController::class, 'create'])->name('groupes.create');
        Route::get('/groupes/co-formateurs/recherche', [GroupeController::class, 'searchCoFormateurs'])->name('groupes.co-formateurs.search');
        Route::post('/groupes', [GroupeController::class, 'store'])->name('groupes.store');
        Route::get('/groupes/{id}/edit', [GroupeController::class, 'edit'])->name('groupes.edit');
        Route::put('/groupes/{id}', [GroupeController::class, 'update'])->name('groupes.update');
        Route::delete('/groupes/{id}', [GroupeController::class, 'destroy'])->name('groupes.destroy');
        Route::prefix('/groupes/{group}/tableau-blanc')
            ->name('groupes.whiteboard.')
            ->group(function () {
                Route::get('/', [WhiteboardController::class, 'show'])->name('show');
                Route::get('/snapshot', [WhiteboardController::class, 'snapshot'])->name('snapshot');
                Route::post('/excalidraw-save', [WhiteboardController::class, 'save'])->name('excalidraw.save');
                Route::post('/items', [WhiteboardController::class, 'upsert'])->name('items.upsert');
                Route::delete('/items/{item}', [WhiteboardController::class, 'destroy'])->name('items.destroy');
                Route::post('/clear', [WhiteboardController::class, 'clear'])->name('clear');
            });

        // ✍️ Émargement (feuille de présence, par groupe)
        Route::get('/emargement', [EmargementController::class, 'index'])->name('emargement.index');
        Route::post('/emargement/groupes/{group}/activer', [EmargementController::class, 'activerGroupe'])->name('emargement.activer');
        Route::post('/emargement/groupes/{group}/desactiver', [EmargementController::class, 'desactiverGroupe'])->name('emargement.desactiver');
        Route::prefix('/groupes/{group}/emargement')
            ->name('groupes.emargement.')
            ->group(function () {
                Route::post('/', [EmargementController::class, 'store'])->name('store');
                Route::get('/{seance}', [EmargementController::class, 'show'])->name('show');
                Route::get('/{seance}/state', [EmargementController::class, 'state'])->name('state');
                Route::post('/{seance}/ouvrir', [EmargementController::class, 'ouvrir'])->name('ouvrir');
                Route::post('/{seance}/fermer', [EmargementController::class, 'fermer'])->name('fermer');
                Route::post('/{seance}/presences/{presence}/corriger', [EmargementController::class, 'corrigerPresence'])->name('presences.corriger');
                Route::get('/{seance}/export-pdf', [EmargementController::class, 'exportPdf'])->name('export-pdf');
            });

        // 🌥️ Nuages de mots (parcours, par groupe)
        Route::prefix('/groupes/{group}/wordcloud')
            ->name('groupes.wordcloud.')
            ->group(function () {
                Route::get('/{item}/live', [GroupeWordCloudController::class, 'live'])->name('live');
                Route::get('/{item}/data', [GroupeWordCloudController::class, 'liveData'])->name('data');
            });

        // 📈 Progression des stagiaires
        Route::get('/progressions', function () {
            return redirect()->route('formateur.progressions.groupes');
        })->name('progressions.index');

        Route::get('/progressions/groupes', [ProgressionGroupesController::class, 'index'])
            ->name('progressions.groupes');

        Route::get('/progressions/stagiaires', [ProgressionStagiairesController::class, 'index'])
            ->name('progressions.stagiaires');

        Route::get('/progressions/stagiaire/{user}', [ProgressionStagiaireController::class, 'show'])
            ->name('progressions.stagiaire');

        Route::get('/progressions/modules', [ProgressionModulesController::class, 'index'])
            ->name('progressions.modules');

        // ✅ IMPORTANT : route AJAX/SCORM
        Route::post('/progression/complete', [ProgressionController::class, 'markCompleted'])
            ->name('progression.complete');

        // 🛠️ Outils numériques
        Route::get('/outils-numeriques', [OutilsNumeriquesController::class, 'index'])
            ->name('outils.index');

        Route::get('/quiz-en-direct', [OutilsLiveQuizController::class, 'index'])
            ->name('outils.quiz.index');

        Route::prefix('/sondages')->name('sondages.')->group(function () {
            Route::get('/', [OutilsSondageController::class, 'index'])->name('index');
            Route::post('/', [OutilsSondageController::class, 'store'])->name('store');
            Route::get('/{pollSession}', [OutilsSondageController::class, 'show'])->name('show');
            Route::post('/{pollSession}/toggle', [OutilsSondageController::class, 'toggle'])->name('toggle');
            Route::get('/{pollSession}/state', [OutilsSondageController::class, 'state'])->name('state');
        });

        Route::prefix('/echelle')->name('echelle.')->group(function () {
            Route::get('/', [OutilsEchelleController::class, 'index'])->name('index');
            Route::post('/', [OutilsEchelleController::class, 'store'])->name('store');
            Route::get('/{scaleSession}', [OutilsEchelleController::class, 'show'])->name('show');
            Route::post('/{scaleSession}/toggle', [OutilsEchelleController::class, 'toggle'])->name('toggle');
            Route::get('/{scaleSession}/state', [OutilsEchelleController::class, 'state'])->name('state');
        });

        Route::get('/pages-collaboratives', [OutilsPagesCollaborativesController::class, 'index'])
            ->name('pages-collaboratives.index');

        Route::prefix('/roue-aleatoire')->name('roue.')->group(function () {
            Route::get('/', [RoueAleatoireController::class, 'index'])->name('index');
            Route::post('/', [RoueAleatoireController::class, 'store'])->name('store');
            Route::get('/{session}', [RoueAleatoireController::class, 'show'])->name('show');
            Route::post('/{session}/participants', [RoueAleatoireController::class, 'updateParticipants'])->name('participants');
            Route::post('/{session}/spin', [RoueAleatoireController::class, 'spin'])->name('spin');
            Route::post('/{session}/reset', [RoueAleatoireController::class, 'reset'])->name('reset');
            Route::get('/{session}/state', [RoueAleatoireController::class, 'state'])->name('state');
        });

        Route::prefix('/mur-questions')->name('questions.')->group(function () {
            Route::get('/', [QuestionWallController::class, 'index'])->name('index');
            Route::post('/', [QuestionWallController::class, 'store'])->name('store');
            Route::get('/{wall}', [QuestionWallController::class, 'show'])->name('show');
            Route::post('/{wall}/toggle', [QuestionWallController::class, 'toggle'])->name('toggle');
            Route::post('/{wall}/questions/{question}/status', [QuestionWallController::class, 'updateStatus'])->name('status');
            Route::get('/{wall}/state', [QuestionWallController::class, 'state'])->name('state');
        });

        // 📚 Mes formations créées
        Route::prefix('/mes-formations')->name('mes-formations.')->group(function () {
            Route::get('/', [MesFormationsController::class, 'index'])->name('index');
            Route::get('/create', [MesFormationsController::class, 'create'])->name('create');
            Route::post('/', [MesFormationsController::class, 'store'])->name('store');
            Route::get('/{parcours}', [MesFormationsController::class, 'show'])->name('show');
            Route::get('/{parcours}/edit', [MesFormationsController::class, 'edit'])->name('edit');
            Route::put('/{parcours}', [MesFormationsController::class, 'update'])->name('update');
            Route::delete('/{parcours}', [MesFormationsController::class, 'destroy'])->name('destroy');
        });

        // 📂 Formations
        Route::get('/formations', [FormateurModuleController::class, 'mesModules'])->name('formations.index');
        Route::get('/formations/{module}/detail', [FormateurModuleController::class, 'moduleDetail'])->name('formations.detail');
        Route::get('/formations/{module}/preview', [FormateurModuleController::class, 'preview'])->name('formations.preview');
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

        // 🧩 Mes modules (module builder formateur)
        Route::prefix('/mes-modules')->name('modules.builder.')->group(function () {
            Route::get('/', [ModuleBuilderController::class, 'index'])->name('index');
            Route::get('/creer', [ModuleBuilderController::class, 'create'])->name('create');
            Route::post('/', [ModuleBuilderController::class, 'store'])->name('store');
            Route::post('/generer-ia', [ModuleBuilderController::class, 'generateStructureIA'])->name('generate-structure-ia');
            Route::post('/depuis-catalogue/{catalogModule}', [ModuleBuilderController::class, 'duplicate'])->name('duplicate');
            Route::post('/{module}/images', [ModuleBuilderController::class, 'uploadImage'])->name('images.store');
            Route::post('/{module}/videos', [ModuleBuilderController::class, 'uploadVideo'])->name('videos.store');
            Route::post('/{module}/scorm', [ModuleBuilderController::class, 'uploadScorm'])->name('scorm.store');
            Route::get('/{module}/edition', [ModuleBuilderController::class, 'edit'])->name('edit');
            Route::put('/{module}', [ModuleBuilderController::class, 'update'])->name('update');
            Route::put('/{module}/options', [ModuleBuilderController::class, 'updateOptions'])->name('options.update');
            Route::delete('/{module}', [ModuleBuilderController::class, 'destroy'])->name('destroy');

            Route::post('/{module}/sections', [ModuleBuilderController::class, 'storeSection'])->name('sections.store');
            Route::put('/sections/{section}', [ModuleBuilderController::class, 'updateSection'])->name('sections.update');
            Route::delete('/sections/{section}', [ModuleBuilderController::class, 'destroySection'])->name('sections.destroy');
            Route::post('/{module}/sections/reorder', [ModuleBuilderController::class, 'reorderSections'])->name('sections.reorder');

            Route::post('/sections/{section}/lectures', [ModuleBuilderController::class, 'storeLecture'])->name('lectures.store');
            Route::post('/sections/{section}/lectures/generer-ia', [ModuleBuilderController::class, 'generateLectureIA'])->name('lectures.generate-ia');
            Route::get('/lectures/{lecture}/edition', [ModuleBuilderController::class, 'editLecture'])->name('lectures.edit');
            Route::post('/lectures/{lecture}/generer-audio', [ModuleBuilderController::class, 'generateAudioLecture'])->name('lectures.generate-audio');
            Route::put('/lectures/{lecture}', [ModuleBuilderController::class, 'updateLecture'])->name('lectures.update');
            Route::delete('/lectures/{lecture}', [ModuleBuilderController::class, 'destroyLecture'])->name('lectures.destroy');
            Route::post('/lectures/{lecture}/duplicate', [ModuleBuilderController::class, 'duplicateLecture'])->name('lectures.duplicate');
            Route::post('/sections/{section}/lectures/reorder', [ModuleBuilderController::class, 'reorderLectures'])->name('lectures.reorder');
            Route::post('/lectures/{lecture}/move', [ModuleBuilderController::class, 'moveLecture'])->name('lectures.move');
            Route::post('/lectures/{lecture}/promote', [ModuleBuilderController::class, 'promoteLectureToSection'])->name('lectures.promote');

            Route::put('/{module}/groupes', [ModuleBuilderController::class, 'assignGroups'])->name('groups.sync');
        });

        Route::redirect('/word-clouds', '/formateur/nuages-de-mots', 301);

        Route::prefix('/nuages-de-mots')
            ->name('nuages.')
            ->group(function () {
                Route::get('/', [FormateurWordCloudController::class, 'index'])->name('index');
                Route::post('/', [FormateurWordCloudController::class, 'store'])->name('store');
                Route::get('/{wordCloud}/live', [FormateurWordCloudController::class, 'live'])->name('live');
                Route::get('/{wordCloud}/live/data', [FormateurWordCloudController::class, 'liveData'])->name('live.data');
            });

        // Personnaliser les leçons d'un module pour un groupe
        Route::get('/groupes/{group}/modules/{module}/lecons', [GroupeModuleLessonController::class, 'editModuleLessons'])
            ->name('groupes.modules.lecons.edit');

        Route::post('/groupes/{group}/modules/{module}/lecons/{lecture}/toggle', [GroupeModuleLessonController::class, 'toggleModuleLesson'])
            ->name('groupes.modules.lecons.toggle');

        Route::post('/groupes/{group}/modules/{module}/lecons/{lecture}/move-up', [GroupeModuleLessonController::class, 'moveModuleLessonUp'])
            ->name('groupes.modules.lecons.move.up');

        Route::post('/groupes/{group}/modules/{module}/lecons/{lecture}/move-down', [GroupeModuleLessonController::class, 'moveModuleLessonDown'])
            ->name('groupes.modules.lecons.move.down');

        Route::post('/groupes/{group}/modules/{module}/lecons/reset', [GroupeModuleLessonController::class, 'resetModuleLessons'])
            ->name('groupes.modules.lecons.reset');

        // Dans le groupe middleware(['auth', 'role:formateur']) ...

        // Route pour modifier le nombre de questions du quiz (Ajax ou Post classique)
        Route::post('/formations/lecture/{lecture}/update-quiz-count', [FormateurModuleController::class, 'updateQuizCount'])
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
