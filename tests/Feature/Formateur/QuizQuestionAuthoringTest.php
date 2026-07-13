<?php

use App\Models\Category;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createQuizAuthoringUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' Auteur',
        'username' => $role.'_auteur_'.uniqid(),
        'email' => $role.'.auteur.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

/**
 * @return array{module: Module, section: ModuleSection, lecture: ModuleLecture}
 */
function createQuizAuthoringLecture(User $owner): array
{
    $suffix = uniqid();

    $category = Category::query()->create([
        'category_name' => 'Categorie auteur '.$suffix,
        'category_slug' => 'categorie-auteur-'.$suffix,
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie auteur '.$suffix,
        'subcategory_slug' => 'sous-categorie-auteur-'.$suffix,
    ]);

    $module = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $owner->id,
        'is_trainer_authored' => true,
        'module_title' => 'Module auteur '.$suffix,
        'module_name' => 'Module auteur '.$suffix,
        'module_name_slug' => 'module-auteur-'.$suffix,
        'status' => 1,
    ]);

    $section = ModuleSection::query()->create([
        'module_id' => $module->id,
        'section_title' => 'Section auteur',
    ]);

    $lecture = ModuleLecture::query()->create([
        'module_id' => $module->id,
        'section_id' => $section->id,
        'lecture_title' => 'Lecon auteur',
        'quiz_enabled' => false,
        'quiz_questions_per_attempt' => 1,
    ]);

    return compact('module', 'section', 'lecture');
}

it('lets the owning trainer create, update and delete a quiz question, tagging it with created_by', function () {
    $formateurA = createQuizAuthoringUser('formateur');
    ['lecture' => $lecture] = createQuizAuthoringLecture($formateurA);

    $storeToken = 'csrf-quiz-author-store';
    $storeResponse = $this->actingAs($formateurA)
        ->withSession(['_token' => $storeToken])
        ->post(route('formateur.modules.builder.lectures.quiz.questions.store', $lecture), [
            '_token' => $storeToken,
            'question_text' => 'Combien font 2+2 ?',
            'type' => 'single',
            'is_active' => 1,
            'options' => [
                ['text' => '4', 'is_correct' => 1],
                ['text' => '5', 'is_correct' => 0],
            ],
        ]);

    $storeResponse->assertRedirect(route('formateur.modules.builder.lectures.quiz.questions.index', $lecture));

    $question = QuizQuestion::query()->where('lecture_id', $lecture->id)->firstOrFail();
    expect($question->created_by)->toBe($formateurA->id);
    expect($question->options()->count())->toBe(2);

    $updateToken = 'csrf-quiz-author-update';
    $updateResponse = $this->actingAs($formateurA)
        ->withSession(['_token' => $updateToken])
        ->put(route('formateur.modules.builder.lectures.quiz.questions.update', ['lecture' => $lecture, 'question' => $question]), [
            '_token' => $updateToken,
            'question_text' => 'Combien font 2+2 (modifie) ?',
            'type' => 'single',
            'is_active' => 1,
            'options' => [
                ['text' => '4', 'is_correct' => 1],
                ['text' => '6', 'is_correct' => 0],
            ],
        ]);

    $updateResponse->assertRedirect(route('formateur.modules.builder.lectures.quiz.questions.index', $lecture));
    expect($question->fresh()->question_text)->toBe('Combien font 2+2 (modifie) ?');

    $deleteToken = 'csrf-quiz-author-delete';
    $deleteResponse = $this->actingAs($formateurA)
        ->withSession(['_token' => $deleteToken])
        ->delete(route('formateur.modules.builder.lectures.quiz.questions.destroy', ['lecture' => $lecture, 'question' => $question]), [
            '_token' => $deleteToken,
        ]);

    $deleteResponse->assertRedirect(route('formateur.modules.builder.lectures.quiz.questions.index', $lecture));
    expect(QuizQuestion::query()->find($question->id))->toBeNull();
});

it('forbids a trainer from managing quiz questions on another trainer lecture', function () {
    $formateurA = createQuizAuthoringUser('formateur');
    $formateurB = createQuizAuthoringUser('formateur');
    ['lecture' => $lecture] = createQuizAuthoringLecture($formateurA);

    $question = QuizQuestion::query()->create([
        'lecture_id' => $lecture->id,
        'question_text' => 'Question de formateur A',
        'type' => 'single',
        'is_active' => true,
        'created_by' => $formateurA->id,
    ]);
    QuizOption::query()->create(['question_id' => $question->id, 'option_text' => 'A', 'is_correct' => 1, 'position' => 1]);
    QuizOption::query()->create(['question_id' => $question->id, 'option_text' => 'B', 'is_correct' => 0, 'position' => 2]);

    $this->actingAs($formateurB)
        ->get(route('formateur.modules.builder.lectures.quiz.questions.index', $lecture))
        ->assertForbidden();

    $storeToken = 'csrf-quiz-forbidden-store';
    $this->actingAs($formateurB)
        ->withSession(['_token' => $storeToken])
        ->post(route('formateur.modules.builder.lectures.quiz.questions.store', $lecture), [
            '_token' => $storeToken,
            'question_text' => 'Intrusion',
            'type' => 'single',
            'options' => [
                ['text' => 'X', 'is_correct' => 1],
                ['text' => 'Y', 'is_correct' => 0],
            ],
        ])
        ->assertForbidden();

    $this->actingAs($formateurB)
        ->get(route('formateur.modules.builder.lectures.quiz.questions.edit', ['lecture' => $lecture, 'question' => $question]))
        ->assertForbidden();

    $updateToken = 'csrf-quiz-forbidden-update';
    $this->actingAs($formateurB)
        ->withSession(['_token' => $updateToken])
        ->put(route('formateur.modules.builder.lectures.quiz.questions.update', ['lecture' => $lecture, 'question' => $question]), [
            '_token' => $updateToken,
            'question_text' => 'Intrusion modifiee',
            'type' => 'single',
            'options' => [
                ['text' => 'X', 'is_correct' => 1],
                ['text' => 'Y', 'is_correct' => 0],
            ],
        ])
        ->assertForbidden();

    $destroyToken = 'csrf-quiz-forbidden-destroy';
    $this->actingAs($formateurB)
        ->withSession(['_token' => $destroyToken])
        ->delete(route('formateur.modules.builder.lectures.quiz.questions.destroy', ['lecture' => $lecture, 'question' => $question]), [
            '_token' => $destroyToken,
        ])
        ->assertForbidden();

    expect(QuizQuestion::query()->find($question->id))->not->toBeNull();
});

it('enforces the same option validation rules as the admin question bank', function () {
    $formateurA = createQuizAuthoringUser('formateur');
    ['lecture' => $lecture] = createQuizAuthoringLecture($formateurA);

    $token = 'csrf-quiz-author-invalid';
    $response = $this->actingAs($formateurA)
        ->withSession(['_token' => $token])
        ->post(route('formateur.modules.builder.lectures.quiz.questions.store', $lecture), [
            '_token' => $token,
            'question_text' => 'Question sans bonne reponse',
            'type' => 'single',
            'options' => [
                ['text' => 'A', 'is_correct' => 0],
                ['text' => 'B', 'is_correct' => 0],
            ],
        ]);

    $response->assertSessionHasErrors('options');
    expect(QuizQuestion::query()->where('lecture_id', $lecture->id)->count())->toBe(0);
});

it('lets the owning trainer update the quiz settings but forbids a non-owner', function () {
    $formateurA = createQuizAuthoringUser('formateur');
    $formateurB = createQuizAuthoringUser('formateur');
    ['lecture' => $lecture] = createQuizAuthoringLecture($formateurA);

    $question = QuizQuestion::query()->create([
        'lecture_id' => $lecture->id,
        'question_text' => 'Question banque',
        'type' => 'single',
        'is_active' => true,
    ]);
    QuizOption::query()->create(['question_id' => $question->id, 'option_text' => 'A', 'is_correct' => 1, 'position' => 1]);
    QuizOption::query()->create(['question_id' => $question->id, 'option_text' => 'B', 'is_correct' => 0, 'position' => 2]);

    $ownerToken = 'csrf-quiz-settings-owner';
    $ownerResponse = $this->actingAs($formateurA)
        ->withSession(['_token' => $ownerToken])
        ->post(route('formateur.lecture.update_quiz_count', $lecture), [
            '_token' => $ownerToken,
            'questions_count' => 1,
            'quiz_enabled' => 1,
        ]);

    $ownerResponse->assertRedirect();
    $lecture->refresh();
    expect((bool) $lecture->quiz_enabled)->toBeTrue();
    expect((int) $lecture->quiz_questions_per_attempt)->toBe(1);

    $intruderToken = 'csrf-quiz-settings-intruder';
    $this->actingAs($formateurB)
        ->withSession(['_token' => $intruderToken])
        ->post(route('formateur.lecture.update_quiz_count', $lecture), [
            '_token' => $intruderToken,
            'questions_count' => 1,
        ])
        ->assertForbidden();
});
