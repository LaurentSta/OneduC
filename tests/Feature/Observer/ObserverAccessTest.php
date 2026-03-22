<?php

use App\Models\Group;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\User;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\DB;

it('redirects an authenticated observateur to the observateur dashboard from the login page', function () {
    $observer = User::factory()->observateur()->create([
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($observer)
        ->get(route('connexion'))
        ->assertRedirect(route('observateur.dashboard'));
});

it('shows only the observed groups to an observateur', function () {
    $formateur = User::factory()->create([
        'role' => 'formateur',
    ]);

    $observer = User::factory()->observateur()->create();

    $visibleGroup = Group::query()->create([
        'name' => 'Groupe visible',
        'description' => 'Visible',
        'instructor_id' => $formateur->id,
    ]);

    $hiddenGroup = Group::query()->create([
        'name' => 'Groupe caché',
        'description' => 'Cache',
        'instructor_id' => $formateur->id,
    ]);

    DB::table('group_user')->insert([
        'group_id' => $visibleGroup->id,
        'user_id' => $observer->id,
        'role_in_group' => 'observateur',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($observer)
        ->get(route('observateur.groupes.index'))
        ->assertOk()
        ->assertSee('Groupe visible')
        ->assertDontSee('Groupe caché');
});

it('forbids an observateur from opening a module path for an unassigned group', function () {
    $formateur = User::factory()->create([
        'role' => 'formateur',
    ]);

    $observer = User::factory()->observateur()->create();

    $category = Category::query()->create([
        'category_name' => 'Categorie test observateur',
        'category_slug' => 'categorie-test-observateur',
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie test observateur',
        'subcategory_slug' => 'sous-categorie-test-observateur',
    ]);

    $allowedGroup = Group::query()->create([
        'name' => 'Groupe autorisé',
        'description' => 'Autorisé',
        'instructor_id' => $formateur->id,
    ]);

    $forbiddenGroup = Group::query()->create([
        'name' => 'Groupe interdit',
        'description' => 'Interdit',
        'instructor_id' => $formateur->id,
    ]);

    DB::table('group_user')->insert([
        'group_id' => $allowedGroup->id,
        'user_id' => $observer->id,
        'role_in_group' => 'observateur',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $module = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module observe',
        'module_name' => 'Module observe',
        'module_name_slug' => 'module-observe',
        'status' => 1,
    ]);

    DB::table('group_module')->insert([
        ['group_id' => $allowedGroup->id, 'module_id' => $module->id],
        ['group_id' => $forbiddenGroup->id, 'module_id' => $module->id],
    ]);

    $section = ModuleSection::query()->create([
        'module_id' => $module->id,
        'section_title' => 'Section 1',
    ]);

    ModuleLecture::query()->create([
        'module_id' => $module->id,
        'section_id' => $section->id,
        'lecture_title' => 'Leçon 1',
        'position' => 1,
    ]);

    $this->actingAs($observer)
        ->get(route('observateur.groupes.modules.lecons.show', ['group' => $forbiddenGroup->id, 'module' => $module->id]))
        ->assertNotFound();
});
