<?php

use App\Models\Category;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\QuizQuestion;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createQuizMediaUser(string $role): User
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
function createQuizMediaLectureContext(User $owner): array
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

it('keeps the existing image when updating a quiz question without requesting removal', function () {
    Storage::fake('public');

    $admin = createQuizMediaUser('admin');
    $context = createQuizMediaLectureContext($admin);
    $lecture = $context['lecture'];

    $image = UploadedFile::fake()->image('question.png');
    $storedPath = $image->store("quiz/questions/lecture_{$lecture->id}", 'public');

    $question = QuizQuestion::query()->create([
        'lecture_id' => $lecture->id,
        'question_text' => 'Question initiale',
        'type' => 'single',
        'is_active' => true,
        'image_path' => $storedPath,
        'image_alt' => 'Illustration initiale',
    ]);

    $token = 'test-token';

    $response = $this->withSession(['_token' => $token])->actingAs($admin)->post(route('admin.quiz.questions.update', [
        'lecture' => $lecture->id,
        'question' => $question->id,
    ]), [
        '_token' => $token,
        '_method' => 'PUT',
        'question_text' => 'Question mise a jour',
        'type' => 'single',
        'is_active' => 1,
        'image_alt' => 'Illustration initiale mise a jour',
        'options' => [
            ['text' => 'Option A', 'is_correct' => 1],
            ['text' => 'Option B', 'is_correct' => 0],
        ],
        'remove_image' => 0,
    ]);

    $response->assertRedirect(route('admin.quiz.questions.index', ['lecture' => $lecture->id]));

    $question->refresh();

    expect($question->image_path)->toBe($storedPath);
    expect($question->image_alt)->toBe('Illustration initiale mise a jour');
    Storage::disk('public')->assertExists($storedPath);
});
