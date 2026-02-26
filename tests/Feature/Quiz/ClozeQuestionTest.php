<?php

use App\Models\Category;
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

function createQuizUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role) . ' Test',
        'username' => $role . '_' . uniqid(),
        'email' => $role . '.' . uniqid() . '@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

/**
 * @return array{module: Module, section: ModuleSection, lecture: ModuleLecture}
 */
function createQuizLectureContext(User $owner): array
{
    $suffix = uniqid();

    $category = Category::query()->create([
        'category_name' => 'Categorie ' . $suffix,
        'category_slug' => 'categorie-' . $suffix,
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie ' . $suffix,
        'subcategory_slug' => 'sous-categorie-' . $suffix,
    ]);

    $module = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $owner->id,
        'module_title' => 'Module test ' . $suffix,
        'module_name' => 'Module test ' . $suffix,
        'module_name_slug' => 'module-test-' . $suffix,
        'status' => 1,
    ]);

    $section = ModuleSection::query()->create([
        'module_id' => $module->id,
        'section_title' => 'Section test',
    ]);

    $lecture = ModuleLecture::query()->create([
        'module_id' => $module->id,
        'section_id' => $section->id,
        'lecture_title' => 'Lecon test',
        'quiz_enabled' => true,
        'quiz_questions_per_attempt' => 1,
    ]);

    return [
        'module' => $module,
        'section' => $section,
        'lecture' => $lecture,
    ];
}

it('stores a cloze payload when an admin creates a cloze question', function () {
    $admin = createQuizUser('admin');
    $context = createQuizLectureContext($admin);
    $lecture = $context['lecture'];

    $response = $this->actingAs($admin)->post(route('admin.quiz.questions.store', [
        'lecture' => $lecture->id,
    ]), [
        'question_text' => 'Completez la formule.',
        'type' => 'cloze',
        'is_active' => 1,
        'cloze_raw_text' => '=RECHERCHEV({{search_val}}; {{matrix}}; {{col_index}}; FAUX)',
        'cloze_blanks' => [
            'search_val' => [
                'accepted_answers' => 'valeur_cherchee, valeur cherchée',
                'points' => 1,
            ],
            'matrix' => [
                'accepted_answers' => 'matrice_table; matrice',
                'points' => 1,
            ],
            'col_index' => [
                'accepted_answers' => 'no_index_col, index',
                'points' => 1,
            ],
        ],
    ]);

    $response->assertRedirect(route('admin.quiz.questions.index', ['lecture' => $lecture->id]));

    $question = QuizQuestion::query()->latest('id')->first();

    expect($question)->not->toBeNull();
    expect($question->type)->toBe('cloze');
    expect(data_get($question->payload, 'raw_text'))->toBe('=RECHERCHEV({{search_val}}; {{matrix}}; {{col_index}}; FAUX)');
    expect(data_get($question->payload, 'blanks.search_val.accepted_answers'))->toContain('valeur_cherchee');
    expect((int) data_get($question->payload, 'blanks.col_index.points'))->toBe(1);
    expect($question->options()->count())->toBe(0);
});

it('grades cloze answers with partial points and finalizes attempt score in percent', function () {
    $stagiaire = createQuizUser('stagiaire');
    $context = createQuizLectureContext($stagiaire);
    $module = $context['module'];
    $section = $context['section'];
    $lecture = $context['lecture'];

    $question = QuizQuestion::query()->create([
        'lecture_id' => $lecture->id,
        'question_text' => 'Completez la formule Excel.',
        'type' => 'cloze',
        'is_active' => true,
        'payload' => [
            'raw_text' => '=RECHERCHEV({{search_val}}; {{matrix}}; {{col_index}}; FAUX)',
            'blanks' => [
                'search_val' => [
                    'accepted_answers' => ['valeur_cherchee', 'valeur cherchée'],
                    'points' => 1,
                ],
                'matrix' => [
                    'accepted_answers' => ['matrice_table', 'matrice'],
                    'points' => 1,
                ],
                'col_index' => [
                    'accepted_answers' => ['index', 'numero index'],
                    'points' => 1,
                ],
            ],
        ],
    ]);

    $attempt = QuizAttempt::query()->create([
        'user_id' => $stagiaire->id,
        'lecture_id' => $lecture->id,
        'started_at' => now(),
        'total_questions' => 1,
        'score' => 0,
        'percent' => 0,
        'passed' => 0,
        'total_time_seconds' => 0,
    ]);

    $attemptQuestion = QuizAttemptQuestion::query()->create([
        'attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'position' => 1,
        'time_seconds' => 0,
    ]);

    $response = $this->actingAs($stagiaire)->post(route('stagiaire.lesson.quiz.answer', [
        'module' => $module->id,
        'section' => $section->id,
        'lecture' => $lecture->id,
        'attempt' => $attempt->id,
    ]), [
        'answers' => [
            'search_val' => 'Valeur cherchée',
            'matrix' => ' matrice ',
            'col_index' => 'incorrect',
        ],
    ]);

    $response->assertRedirect(route('stagiaire.lesson.quiz.result', [
        'module' => $module->id,
        'section' => $section->id,
        'lecture' => $lecture->id,
        'attempt' => $attempt->id,
    ]));

    $attemptQuestion->refresh();
    $given = $attemptQuestion->given_answer;

    expect($attemptQuestion->is_correct)->toBeFalse();
    expect((int) data_get($given, 'score'))->toBe(2);
    expect((int) data_get($given, 'max_score'))->toBe(3);
    expect((int) data_get($given, 'percent'))->toBe(67);
    expect((bool) data_get($given, 'blanks.search_val.is_correct'))->toBeTrue();
    expect((bool) data_get($given, 'blanks.col_index.is_correct'))->toBeFalse();

    $attempt->refresh();
    expect((int) $attempt->score)->toBe(67);
    expect((int) $attempt->percent)->toBe(67);
    expect((bool) $attempt->passed)->toBeTrue();
});
