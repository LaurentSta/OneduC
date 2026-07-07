<?php

use App\Models\LessonFeedback;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function seedLessonFeedbackContext(): array
{
    $categoryId = DB::table('categories')->insertGetId([
        'category_name' => 'Numerique',
        'category_description' => 'Tests',
        'category_slug' => 'numerique',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $subCategoryId = DB::table('subcategories')->insertGetId([
        'category_id' => $categoryId,
        'subcategory_name' => 'Bureautique',
        'subcategory_description' => 'Tests',
        'subcategory_slug' => 'bureautique',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $formateur = User::query()->create([
        'prenom' => 'Claire',
        'name' => 'Formatrice',
        'username' => 'claire.formatrice',
        'email' => 'claire.formatrice@example.test',
        'password' => Hash::make('password'),
        'role' => 'formateur',
        'status' => true,
    ]);

    $stagiaire = User::query()->create([
        'prenom' => 'Leo',
        'name' => 'Stagiaire',
        'username' => 'leo.stagiaire',
        'email' => 'leo.stagiaire@example.test',
        'password' => Hash::make('password'),
        'role' => 'stagiaire',
        'status' => true,
        'password_changed_at' => now(),
    ]);

    $module = Module::query()->create([
        'category_id' => $categoryId,
        'subcategory_id' => $subCategoryId,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module feedback',
        'module_name' => 'Module feedback',
        'module_name_slug' => 'module-feedback',
        'status' => true,
    ]);

    $section = ModuleSection::query()->create([
        'module_id' => $module->id,
        'section_title' => 'Section 1',
    ]);

    $lecture = ModuleLecture::query()->create([
        'module_id' => $module->id,
        'section_id' => $section->id,
        'lecture_title' => 'Lecon 1',
        'position' => 1,
        'content_type' => 'scorm',
    ]);

    return compact('stagiaire', 'lecture');
}

test('an authenticated stagiaire can submit lesson feedback and is redirected back', function () {
    ['stagiaire' => $stagiaire, 'lecture' => $lecture] = seedLessonFeedbackContext();

    $response = $this
        ->actingAs($stagiaire)
        ->from('/lecture/'.$lecture->id)
        ->post('/feedback', [
            'lesson_id' => $lecture->id,
            'comment' => 'Je n’ai pas compris cette partie.',
            'type' => 'incomprehension',
            'rating' => 3,
            'urgency' => 2,
        ]);

    $response->assertRedirect('/lecture/'.$lecture->id);
    $response->assertSessionHas('success');

    expect(LessonFeedback::where('lesson_id', $lecture->id)->where('user_id', $stagiaire->id)->exists())->toBeTrue();
});

test('lesson feedback submission requires authentication', function () {
    ['lecture' => $lecture] = seedLessonFeedbackContext();

    $response = $this->post('/feedback', [
        'lesson_id' => $lecture->id,
        'comment' => 'Anonyme',
    ]);

    $response->assertRedirect('/login');
    $this->assertDatabaseCount('lesson_feedbacks', 0);
});
