<?php

use App\Models\Category;
use App\Models\Group;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function seedModuleVisibilityContext(array $moduleOverrides = []): array
{
    $category = Category::query()->create([
        'category_name' => 'Categorie visibilite '.uniqid(),
        'category_slug' => 'categorie-visibilite-'.uniqid(),
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie visibilite',
        'subcategory_slug' => 'sous-categorie-visibilite-'.uniqid(),
    ]);

    $owner = User::factory()->create(['role' => 'formateur']);

    $module = Module::query()->create(array_merge([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $owner->id,
        'module_title' => 'Module visibilite '.uniqid(),
        'module_name' => 'Module visibilite',
        'module_name_slug' => 'module-visibilite-'.uniqid(),
        'status' => 1,
        'is_trainer_authored' => true,
    ], $moduleOverrides));

    $section = ModuleSection::query()->create([
        'module_id' => $module->id,
        'section_title' => 'Section 1',
    ]);

    $lecture = ModuleLecture::query()->create([
        'module_id' => $module->id,
        'section_id' => $section->id,
        'lecture_title' => 'Lecon 1',
        'position' => 1,
        'content_type' => 'blocks',
        'content_blocks' => [],
    ]);

    return compact('owner', 'module', 'section', 'lecture');
}

test('a stagiaire in a group with the module assigned can open the lesson', function () {
    ['owner' => $owner, 'module' => $module, 'section' => $section, 'lecture' => $lecture] = seedModuleVisibilityContext();

    $stagiaire = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $group = Group::query()->create(['name' => 'Groupe visibilite '.uniqid(), 'instructor_id' => $owner->id]);
    DB::table('group_user')->insert(['group_id' => $group->id, 'user_id' => $stagiaire->id, 'role_in_group' => 'stagiaire']);
    DB::table('group_module')->insert(['group_id' => $group->id, 'module_id' => $module->id]);

    $this->actingAs($stagiaire)
        ->get(route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id]))
        ->assertOk();
});

test('a stagiaire with no group assignment cannot open the lesson of an active module', function () {
    ['module' => $module, 'section' => $section, 'lecture' => $lecture] = seedModuleVisibilityContext();

    $stagiaire = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $this->actingAs($stagiaire)
        ->get(route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id]))
        ->assertNotFound();
});

test('the owning formateur can open the lesson of their own trainer-authored module', function () {
    ['owner' => $owner, 'module' => $module, 'section' => $section, 'lecture' => $lecture] = seedModuleVisibilityContext();

    $this->actingAs($owner)
        ->get(route('formateur.formations.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id]))
        ->assertOk();
});

test('a foreign formateur cannot open the lesson of a trainer-authored module they do not teach', function () {
    ['module' => $module, 'section' => $section, 'lecture' => $lecture] = seedModuleVisibilityContext();

    $otherFormateur = User::factory()->create(['role' => 'formateur']);

    $this->actingAs($otherFormateur)
        ->get(route('formateur.formations.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id]))
        ->assertNotFound();
});

test('any formateur can open the lesson of a catalog module regardless of ownership', function () {
    ['module' => $module, 'section' => $section, 'lecture' => $lecture] = seedModuleVisibilityContext(['is_trainer_authored' => false]);

    $otherFormateur = User::factory()->create(['role' => 'formateur']);

    $this->actingAs($otherFormateur)
        ->get(route('formateur.formations.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id]))
        ->assertOk();
});
