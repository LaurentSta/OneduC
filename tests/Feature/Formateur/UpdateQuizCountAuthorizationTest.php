<?php

use App\Models\Category;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\SubCategory;
use App\Models\User;

function createUpdateQuizCountModule(User $formateur): array
{
    $category = Category::query()->create([
        'category_name' => 'Categorie quiz count '.uniqid(),
        'category_slug' => 'categorie-quiz-count-'.uniqid(),
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie quiz count',
        'subcategory_slug' => 'sous-categorie-quiz-count-'.uniqid(),
    ]);

    $module = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module quiz count',
        'module_name' => 'Module quiz count',
        'module_name_slug' => 'module-quiz-count-'.uniqid(),
        'status' => 1,
    ]);

    $section = ModuleSection::query()->create([
        'module_id' => $module->id,
        'section_title' => 'Chapitre',
    ]);

    $lecture = ModuleLecture::query()->create([
        'module_id' => $module->id,
        'section_id' => $section->id,
        'lecture_title' => 'Lecon quiz',
        'position' => 1,
        'quiz_questions_per_attempt' => 1,
    ]);

    return [$module, $lecture];
}

it('blocks a trainer from updating the quiz count of a lecture owned by another trainer', function () {
    $owner = User::factory()->create(['role' => 'formateur']);
    $intruder = User::factory()->create(['role' => 'formateur']);

    [, $lecture] = createUpdateQuizCountModule($owner);

    $response = $this->actingAs($intruder)
        ->post(route('formateur.lecture.update_quiz_count', ['lecture' => $lecture->id]), [
            'questions_count' => 1,
        ]);

    $response->assertForbidden();
});

it('allows the owning trainer to update the quiz count of their own lecture', function () {
    $owner = User::factory()->create(['role' => 'formateur']);

    [, $lecture] = createUpdateQuizCountModule($owner);

    $response = $this->actingAs($owner)
        ->post(route('formateur.lecture.update_quiz_count', ['lecture' => $lecture->id]), [
            'questions_count' => 1,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect($lecture->fresh()->quiz_questions_per_attempt)->toBe(1);
});
