<?php

use App\Models\Category;
use App\Models\Group;
use App\Models\Module;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

function createTrainerForEmargementToggleTest(): User
{
    return User::factory()->create([
        'prenom' => 'Formateur',
        'name' => 'EmargementToggle',
        'username' => 'formateur_emargement_toggle_'.uniqid(),
        'email' => 'formateur.emargement.toggle.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => 'formateur',
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function createActiveModuleForEmargementToggleTest(User $formateur): Module
{
    $category = Category::query()->create([
        'category_name' => 'Categorie toggle '.uniqid(),
        'category_slug' => 'categorie-toggle-'.uniqid(),
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous categorie toggle '.uniqid(),
        'subcategory_slug' => 'sous-categorie-toggle-'.uniqid(),
    ]);

    return Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module toggle '.uniqid(),
        'module_name' => 'Module toggle '.uniqid(),
        'module_name_slug' => 'module-toggle-'.uniqid(),
        'status' => 1,
    ]);
}

it('activates emargement for a group at creation time', function () {
    $formateur = createTrainerForEmargementToggleTest();
    $module = createActiveModuleForEmargementToggleTest($formateur);
    $groupName = 'Groupe toggle creation '.uniqid();
    $token = Str::random(40);

    $this->withSession(['_token' => $token])
        ->actingAs($formateur)
        ->post(route('formateur.groupes.store'), [
            '_token' => $token,
            'nom' => $groupName,
            'description' => 'Groupe de test',
            'is_active' => '1',
            'emargement_enabled' => '1',
            'password' => 'password123',
            'modules' => [$module->id],
            'module_positions' => [$module->id => 1],
            'co_formateurs' => [],
            'stagiaires' => [],
        ])
        ->assertRedirect(route('formateur.groupes.index'));

    $group = Group::where('name', $groupName)->firstOrFail();
    expect($group->emargement_enabled)->toBeTrue();
});

it('leaves emargement disabled at creation when the checkbox is unticked', function () {
    $formateur = createTrainerForEmargementToggleTest();
    $module = createActiveModuleForEmargementToggleTest($formateur);
    $groupName = 'Groupe toggle non coche '.uniqid();
    $token = Str::random(40);

    $this->withSession(['_token' => $token])
        ->actingAs($formateur)
        ->post(route('formateur.groupes.store'), [
            '_token' => $token,
            'nom' => $groupName,
            'description' => 'Groupe de test',
            'is_active' => '1',
            'emargement_enabled' => '0',
            'password' => 'password123',
            'modules' => [$module->id],
            'module_positions' => [$module->id => 1],
            'co_formateurs' => [],
            'stagiaires' => [],
        ])
        ->assertRedirect(route('formateur.groupes.index'));

    $group = Group::where('name', $groupName)->firstOrFail();
    expect($group->emargement_enabled)->toBeFalse();
});

it('toggles emargement on and off when editing a group', function () {
    $formateur = createTrainerForEmargementToggleTest();
    $module = createActiveModuleForEmargementToggleTest($formateur);
    $group = Group::create([
        'name' => 'Groupe toggle edition '.uniqid(),
        'description' => 'Groupe de test',
        'is_active' => true,
        'emargement_enabled' => false,
        'temporary_password' => 'password123',
        'instructor_id' => $formateur->id,
    ]);
    $group->modules()->attach($module->id, ['position' => 1]);
    $token = Str::random(40);

    $this->withSession(['_token' => $token])
        ->actingAs($formateur)
        ->put(route('formateur.groupes.update', $group->id), [
            '_token' => $token,
            'nom' => $group->name,
            'description' => $group->description,
            'is_active' => '1',
            'emargement_enabled' => '1',
            'modules' => [$module->id],
            'module_positions' => [$module->id => 1],
            'co_formateurs' => [],
            'stagiaires' => [],
        ])
        ->assertRedirect(route('formateur.groupes.index'));

    expect($group->fresh()->emargement_enabled)->toBeTrue();

    $this->withSession(['_token' => $token])
        ->actingAs($formateur)
        ->put(route('formateur.groupes.update', $group->id), [
            '_token' => $token,
            'nom' => $group->name,
            'description' => $group->description,
            'is_active' => '1',
            'emargement_enabled' => '0',
            'modules' => [$module->id],
            'module_positions' => [$module->id => 1],
            'co_formateurs' => [],
            'stagiaires' => [],
        ])
        ->assertRedirect(route('formateur.groupes.index'));

    expect($group->fresh()->emargement_enabled)->toBeFalse();
});
