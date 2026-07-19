<?php

use App\Models\Group;
use App\Models\LessonResource;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function seedLessonResourceContext(): array
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
        'module_title' => 'Module ressources',
        'module_name' => 'Module ressources',
        'module_name_slug' => 'module-ressources',
        'is_trainer_authored' => true,
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

    $group = Group::query()->create([
        'name' => 'Groupe ressources '.uniqid(),
        'instructor_id' => $formateur->id,
    ]);

    DB::table('group_user')->insert([
        'group_id' => $group->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
    ]);

    DB::table('group_module')->insert([
        'group_id' => $group->id,
        'module_id' => $module->id,
    ]);

    return compact('formateur', 'stagiaire', 'module', 'section', 'lecture');
}

it('allows a formateur to upload a lesson resource and choose trainee visibility', function () {
    Storage::fake('public');

    ['formateur' => $formateur, 'module' => $module, 'section' => $section, 'lecture' => $lecture] = seedLessonResourceContext();

    $token = 'test-csrf-token';

    $response = $this
        ->withSession(['_token' => $token])
        ->actingAs($formateur)
        ->post(route('formateur.formations.lesson.resources.store', [
            'module' => $module->id,
            'section' => $section->id,
            'lecture' => $lecture->id,
        ]), [
            '_token' => $token,
            'title' => 'Guide PDF',
            'resource_file' => UploadedFile::fake()->create('guide.pdf', 200, 'application/pdf'),
            'is_visible_to_stagiaire' => '1',
        ]);

    $response->assertRedirect();

    $resource = LessonResource::query()->where('lecture_id', $lecture->id)->first();

    expect($resource)->not->toBeNull();
    expect($resource->title)->toBe('Guide PDF');
    expect($resource->is_visible_to_stagiaire)->toBeTrue();

    Storage::disk('public')->assertExists($resource->file_path);
});

it('shows only visible lesson resources to stagiaires', function () {
    Storage::fake('public');

    [
        'stagiaire' => $stagiaire,
        'module' => $module,
        'section' => $section,
        'lecture' => $lecture,
    ] = seedLessonResourceContext();

    LessonResource::query()->create([
        'lecture_id' => $lecture->id,
        'title' => 'Document visible',
        'file_path' => 'lesson-resources/tests/document-visible.pdf',
        'original_name' => 'document-visible.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 1024,
        'is_visible_to_stagiaire' => true,
        'position' => 1,
    ]);

    LessonResource::query()->create([
        'lecture_id' => $lecture->id,
        'title' => 'Document masque',
        'file_path' => 'lesson-resources/tests/document-masque.pdf',
        'original_name' => 'document-masque.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 1024,
        'is_visible_to_stagiaire' => false,
        'position' => 2,
    ]);

    $stagiaireResponse = $this
        ->actingAs($stagiaire)
        ->get(route('stagiaire.module.lecture', [
            'module' => $module->id,
            'section' => $section->id,
            'lecture' => $lecture->id,
        ]));

    $stagiaireResponse->assertOk();
    $stagiaireResponse->assertSee('Document visible');
    $stagiaireResponse->assertDontSee('Document masque');
});
