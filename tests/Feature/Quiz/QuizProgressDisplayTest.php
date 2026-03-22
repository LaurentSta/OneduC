<?php

use App\Models\Category;
use App\Models\Group;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptQuestion;
use App\Models\QuizQuestion;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createQuizProgressUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role) . ' Quiz',
        'username' => $role . '_quiz_' . uniqid(),
        'email' => $role . '.quiz.' . uniqid() . '@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function createQuizProgressContext(): array
{
    $formateur = createQuizProgressUser('formateur');
    $stagiaire = createQuizProgressUser('stagiaire');
    $suffix = uniqid();

    $category = Category::query()->create([
        'category_name' => 'Categorie quiz ' . $suffix,
        'category_slug' => 'categorie-quiz-' . $suffix,
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie quiz ' . $suffix,
        'subcategory_slug' => 'sous-categorie-quiz-' . $suffix,
    ]);

    $module = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module quiz ' . $suffix,
        'module_name' => 'Module quiz ' . $suffix,
        'module_name_slug' => 'module-quiz-' . $suffix,
        'status' => 1,
    ]);

    $section = ModuleSection::query()->create([
        'module_id' => $module->id,
        'section_title' => 'Section quiz',
    ]);

    $lecture = ModuleLecture::query()->create([
        'module_id' => $module->id,
        'section_id' => $section->id,
        'lecture_title' => 'Lecon quiz',
        'quiz_enabled' => true,
        'quiz_questions_per_attempt' => 5,
        'question_count' => 11,
    ]);

    $group = Group::query()->create([
        'name' => 'Groupe quiz ' . $suffix,
        'description' => 'Groupe de test',
        'instructor_id' => $formateur->id,
    ]);

    $group->modules()->attach($module->id, ['position' => 1]);
    $group->users()->attach($stagiaire->id, ['role_in_group' => 'stagiaire']);

    $questions = collect();
    foreach (range(1, 12) as $index) {
        $questions->push(QuizQuestion::query()->create([
            'lecture_id' => $lecture->id,
            'question_text' => 'Question ' . $index,
            'type' => 'single',
            'is_active' => true,
        ]));
    }

    return compact('formateur', 'stagiaire', 'module', 'section', 'lecture', 'group', 'questions');
}

function createQuizAttemptWithQuestions(
    User $user,
    ModuleLecture $lecture,
    iterable $questions,
    int $totalQuestions,
    int $answeredQuestions,
    int $correctQuestions,
    ?string $startedAt = null,
    ?string $finishedAt = null,
    ?int $percent = null,
    ?bool $passed = null
): QuizAttempt {
    $attempt = QuizAttempt::query()->create([
        'user_id' => $user->id,
        'lecture_id' => $lecture->id,
        'started_at' => $startedAt ? now()->parse($startedAt) : now(),
        'finished_at' => $finishedAt ? now()->parse($finishedAt) : null,
        'total_questions' => $totalQuestions,
        'score' => $percent ?? 0,
        'percent' => $percent ?? 0,
        'passed' => $passed ?? false,
        'total_time_seconds' => 0,
    ]);

    $questionIds = collect($questions)->take($totalQuestions)->pluck('id')->values();

    foreach ($questionIds as $index => $questionId) {
        $position = $index + 1;
        $isAnswered = $position <= $answeredQuestions;
        $isCorrect = $position <= $correctQuestions;

        QuizAttemptQuestion::query()->create([
            'attempt_id' => $attempt->id,
            'question_id' => $questionId,
            'position' => $position,
            'answered_at' => $isAnswered ? now()->addSeconds($position) : null,
            'is_correct' => $isAnswered ? $isCorrect : false,
            'time_seconds' => $isAnswered ? 10 : 0,
        ]);
    }

    return $attempt;
}

it('uses the latest native quiz attempt to compute stagiaire module progression', function () {
    $context = createQuizProgressContext();
    $stagiaire = $context['stagiaire'];
    $module = $context['module'];
    $lecture = $context['lecture'];
    $questions = $context['questions'];

    createQuizAttemptWithQuestions(
        $stagiaire,
        $lecture,
        $questions->slice(0, 5),
        5,
        5,
        5,
        '-2 days',
        '-2 days +5 minutes',
        100,
        true,
    );

    createQuizAttemptWithQuestions(
        $stagiaire,
        $lecture,
        $questions->slice(5, 5),
        5,
        1,
        1,
        '-1 hour',
        null,
        0,
        false,
    );

    $response = $this->actingAs($stagiaire)
        ->get(route('stagiaire.modules'));

    $response->assertOk();

    $modules = $response->viewData('modules');
    expect($modules)->toHaveCount(1);
    expect($modules->first()->progression_status)->toBe('in_progress');
    expect((int) $modules->first()->progression_percent)->toBe(0);

    $detailResponse = $this->actingAs($stagiaire)
        ->get(route('stagiaire.module.detail', ['module' => $module->id]));

    $detailResponse->assertOk();

    $lessonStatuses = $detailResponse->viewData('lessonStatuses');
    expect($lessonStatuses[$lecture->id])->toBe('incomplete');
});

it('uses the latest quiz attempt totals in stagiaire results instead of the best historical attempt', function () {
    $context = createQuizProgressContext();
    $stagiaire = $context['stagiaire'];
    $lecture = $context['lecture'];
    $questions = $context['questions'];

    createQuizAttemptWithQuestions(
        $stagiaire,
        $lecture,
        $questions->slice(0, 5),
        5,
        5,
        5,
        '-2 days',
        '-2 days +5 minutes',
        100,
        true,
    );

    createQuizAttemptWithQuestions(
        $stagiaire,
        $lecture,
        $questions->slice(5, 3),
        3,
        3,
        1,
        '-1 hour',
        '-55 minutes',
        33,
        false,
    );

    $response = $this->actingAs($stagiaire)
        ->get(route('stagiaire.resultats'));

    $response->assertOk();

    $resultats = $response->viewData('resultats');
    expect($resultats)->toHaveCount(1);
    expect((int) $resultats->first()->total_questions)->toBe(3);
    expect((int) $resultats->first()->correct_answers)->toBe(1);
    expect((int) $resultats->first()->wrong_answers)->toBe(2);
    expect($resultats->first()->status_key)->toBe('failed');
});

it('shows the trainer configured quiz count in the formateur group lesson view', function () {
    $context = createQuizProgressContext();
    $formateur = $context['formateur'];
    $group = $context['group'];
    $module = $context['module'];
    $lecture = $context['lecture'];

    $lecture->update([
        'quiz_questions_per_attempt' => 4,
        'question_count' => 11,
    ]);

    $response = $this->actingAs($formateur)
        ->get(route('formateur.groupes.modules.lecons.edit', [
            'group' => $group->id,
            'module' => $module->id,
        ]));

    $response->assertOk();
    $response->assertSee('"questions":4', false);
    $response->assertDontSee('"questions":11', false);
});
