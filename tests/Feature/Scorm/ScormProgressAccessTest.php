<?php

use App\Models\Evaluation;
use App\Models\Group;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function seedScormAccessContext(): array
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
        'username' => 'claire.formatrice.scorm',
        'email' => 'claire.formatrice.scorm@example.test',
        'password' => Hash::make('password'),
        'role' => 'formateur',
        'status' => true,
    ]);

    $memberStagiaire = User::query()->create([
        'prenom' => 'Leo',
        'name' => 'Membre',
        'username' => 'leo.membre',
        'email' => 'leo.membre@example.test',
        'password' => Hash::make('password'),
        'role' => 'stagiaire',
        'status' => true,
        'password_changed_at' => now(),
    ]);

    $outsiderStagiaire = User::query()->create([
        'prenom' => 'Sam',
        'name' => 'Horsgroupe',
        'username' => 'sam.horsgroupe',
        'email' => 'sam.horsgroupe@example.test',
        'password' => Hash::make('password'),
        'role' => 'stagiaire',
        'status' => true,
        'password_changed_at' => now(),
    ]);

    $evaluation = Evaluation::query()->create([
        'titre' => 'Evaluation test',
        'scorm_path' => 'evaluations/demo/index.html',
    ]);

    $module = Module::query()->create([
        'category_id' => $categoryId,
        'subcategory_id' => $subCategoryId,
        'formateur_id' => $formateur->id,
        'evaluation_id' => $evaluation->id,
        'module_title' => 'Module scorm access',
        'module_name' => 'Module scorm access',
        'module_name_slug' => 'module-scorm-access',
        'status' => true,
    ]);

    $section = ModuleSection::query()->create([
        'module_id' => $module->id,
        'section_title' => 'Section 1',
    ]);

    $lecture = ModuleLecture::query()->create([
        'module_id' => $module->id,
        'section_id' => $section->id,
        'lecture_title' => 'Lecon scorm',
        'position' => 1,
        'content_type' => 'scorm',
    ]);

    $group = Group::query()->create([
        'name' => 'Groupe scorm access',
        'instructor_id' => $formateur->id,
        'is_active' => true,
    ]);

    DB::table('group_user')->insert([
        'group_id' => $group->id,
        'user_id' => $memberStagiaire->id,
        'role_in_group' => 'stagiaire',
    ]);

    DB::table('group_module')->insert([
        'group_id' => $group->id,
        'module_id' => $module->id,
    ]);

    return compact('memberStagiaire', 'outsiderStagiaire', 'module', 'lecture', 'evaluation');
}

test('a stagiaire whose group has the module assigned can save scorm progress', function () {
    ['memberStagiaire' => $stagiaire, 'lecture' => $lecture] = seedScormAccessContext();

    $response = $this->actingAs($stagiaire)->post('/scorm/save-progress', [
        'lecture_id' => $lecture->id,
        'scorm_key' => 'cmi.core.lesson_status',
        'scorm_value' => 'incomplete',
    ]);

    $response->assertOk();
    $response->assertJson(['success' => true]);
});

test('a stagiaire outside the assigned group cannot save scorm progress', function () {
    ['outsiderStagiaire' => $stagiaire, 'lecture' => $lecture] = seedScormAccessContext();

    $response = $this->actingAs($stagiaire)->post('/scorm/save-progress', [
        'lecture_id' => $lecture->id,
        'scorm_key' => 'cmi.core.lesson_status',
        'scorm_value' => 'incomplete',
    ]);

    $response->assertForbidden();
});

test('an unauthenticated request cannot save scorm progress', function () {
    ['lecture' => $lecture] = seedScormAccessContext();

    $response = $this->post('/scorm/save-progress', [
        'lecture_id' => $lecture->id,
        'scorm_key' => 'cmi.core.lesson_status',
        'scorm_value' => 'incomplete',
    ]);

    $response->assertRedirect('/login');

    $jsonResponse = $this->postJson('/scorm/save-progress', [
        'lecture_id' => $lecture->id,
        'scorm_key' => 'cmi.core.lesson_status',
        'scorm_value' => 'incomplete',
    ]);

    $jsonResponse->assertUnauthorized();
});

test('a stagiaire whose group has the module assigned can save content block scorm progress', function () {
    ['memberStagiaire' => $stagiaire, 'lecture' => $lecture] = seedScormAccessContext();

    $response = $this->actingAs($stagiaire)->post('/scorm/save-block-progress', [
        'lecture_id' => $lecture->id,
        'content_block_key' => 'bloc-scorm-1',
        'scorm_key' => 'cmi.core.lesson_status',
        'scorm_value' => 'incomplete',
    ]);

    $response->assertOk();
    $response->assertJson(['success' => true]);
});

test('a stagiaire outside the assigned group cannot save content block scorm progress', function () {
    ['outsiderStagiaire' => $stagiaire, 'lecture' => $lecture] = seedScormAccessContext();

    $response = $this->actingAs($stagiaire)->post('/scorm/save-block-progress', [
        'lecture_id' => $lecture->id,
        'content_block_key' => 'bloc-scorm-1',
        'scorm_key' => 'cmi.core.lesson_status',
        'scorm_value' => 'incomplete',
    ]);

    $response->assertForbidden();
});

test('an unauthenticated request cannot save content block scorm progress', function () {
    ['lecture' => $lecture] = seedScormAccessContext();

    $response = $this->post('/scorm/save-block-progress', [
        'lecture_id' => $lecture->id,
        'content_block_key' => 'bloc-scorm-1',
        'scorm_key' => 'cmi.core.lesson_status',
        'scorm_value' => 'incomplete',
    ]);

    $response->assertRedirect('/login');
});

test('a stagiaire whose group has the module assigned can save evaluation scorm progress', function () {
    ['memberStagiaire' => $stagiaire, 'evaluation' => $evaluation] = seedScormAccessContext();

    $response = $this->actingAs($stagiaire)->post('/scorm/evaluation-progress', [
        'evaluation_id' => $evaluation->id,
        'scorm_key' => 'cmi.core.lesson_status',
        'scorm_value' => 'incomplete',
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'ok']);
});

test('a stagiaire outside the assigned group cannot save evaluation scorm progress', function () {
    ['outsiderStagiaire' => $stagiaire, 'evaluation' => $evaluation] = seedScormAccessContext();

    $response = $this->actingAs($stagiaire)->post('/scorm/evaluation-progress', [
        'evaluation_id' => $evaluation->id,
        'scorm_key' => 'cmi.core.lesson_status',
        'scorm_value' => 'incomplete',
    ]);

    $response->assertForbidden();
});

test('an unauthenticated request cannot save evaluation scorm progress', function () {
    ['evaluation' => $evaluation] = seedScormAccessContext();

    $response = $this->post('/scorm/evaluation-progress', [
        'evaluation_id' => $evaluation->id,
        'scorm_key' => 'cmi.core.lesson_status',
        'scorm_value' => 'incomplete',
    ]);

    $response->assertRedirect('/login');
});
