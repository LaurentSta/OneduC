<?php

use App\Models\Category;
use App\Models\Group;
use App\Models\Module;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

function createTrainerForCoTrainerTest(string $emailPrefix, bool $active = true): User
{
    return User::factory()->create([
        'prenom' => 'Formateur',
        'name' => ucfirst($emailPrefix),
        'username' => $emailPrefix.'_'.uniqid(),
        'email' => $emailPrefix.'.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => 'formateur',
        'status' => $active,
        'password_changed_at' => now(),
    ]);
}

function createActiveModuleForTrainer(User $formateur): Module
{
    $category = Category::query()->create([
        'category_name' => 'Categorie groupe '.uniqid(),
        'category_slug' => 'categorie-groupe-'.uniqid(),
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous categorie groupe '.uniqid(),
        'subcategory_slug' => 'sous-categorie-groupe-'.uniqid(),
    ]);

    return Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module groupe '.uniqid(),
        'module_name' => 'Module groupe '.uniqid(),
        'module_name_slug' => 'module-groupe-'.uniqid(),
        'status' => 1,
    ]);
}

function createSharedGroupForTrainer(User $owner, Module $module, User $coTrainer): Group
{
    $group = Group::query()->create([
        'name' => 'Groupe partage '.uniqid(),
        'description' => 'Groupe de test partage',
        'is_active' => true,
        'temporary_password' => 'password123',
        'instructor_id' => $owner->id,
    ]);

    DB::table('group_module')->insert([
        'group_id' => $group->id,
        'module_id' => $module->id,
        'position' => 1,
    ]);

    DB::table('group_user')->insert([
        'group_id' => $group->id,
        'user_id' => $coTrainer->id,
        'role_in_group' => 'formateur',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $group;
}

it('attaches selected co-trainers and creates an internal notification when storing a group', function () {
    $owner = createTrainerForCoTrainerTest('owner');
    $coTrainer = createTrainerForCoTrainerTest('co');
    $module = createActiveModuleForTrainer($owner);
    $groupName = 'Groupe wizard co-formateur '.uniqid();
    $token = Str::random(40);

    $response = $this
        ->withSession(['_token' => $token])
        ->actingAs($owner)
        ->post(route('formateur.groupes.store'), [
            '_token' => $token,
            'nom' => $groupName,
            'description' => 'Groupe avec co-formateur',
            'is_active' => '1',
            'password' => 'password123',
            'modules' => [$module->id],
            'module_positions' => [$module->id => 1],
            'co_formateurs' => [$coTrainer->id],
            'stagiaires' => [],
        ]);

    $response->assertRedirect(route('formateur.groupes.index'));

    $group = Group::query()->where('name', $groupName)->firstOrFail();

    $this->assertDatabaseHas('group_user', [
        'group_id' => $group->id,
        'user_id' => $coTrainer->id,
        'role_in_group' => 'formateur',
    ]);

    $notification = DB::table('notifications')
        ->where('notifiable_id', $coTrainer->id)
        ->where('notifiable_type', User::class)
        ->latest('created_at')
        ->first();

    expect($notification)->not->toBeNull();

    $payload = json_decode((string) $notification->data, true, 512, JSON_THROW_ON_ERROR);

    expect($payload['event'] ?? null)->toBe('group_co_trainer_added');
    expect($payload['group_id'] ?? null)->toBe($group->id);
});

it('allows a short temporary access code when creating and editing a group', function () {
    $owner = createTrainerForCoTrainerTest('owner-short-code');
    $module = createActiveModuleForTrainer($owner);
    $groupName = 'Groupe code court '.uniqid();
    $token = Str::random(40);

    $this
        ->withSession(['_token' => $token])
        ->actingAs($owner)
        ->post(route('formateur.groupes.store'), [
            '_token' => $token,
            'nom' => $groupName,
            'description' => 'Groupe avec code court',
            'is_active' => '1',
            'password' => 'A',
            'modules' => [$module->id],
            'module_positions' => [$module->id => 1],
            'co_formateurs' => [],
            'stagiaires' => [],
        ])
        ->assertRedirect(route('formateur.groupes.index'));

    $group = Group::query()->where('name', $groupName)->firstOrFail();

    expect($group->temporary_password)->toBe('A');

    $this
        ->withSession(['_token' => $token])
        ->actingAs($owner)
        ->put(route('formateur.groupes.update', $group->id), [
            '_token' => $token,
            'nom' => $groupName.' modifie',
            'description' => 'Code court conserve',
            'is_active' => '1',
            'password' => 'B',
            'modules' => [$module->id],
            'module_positions' => [$module->id => 1],
            'co_formateurs' => [],
            'stagiaires' => [],
        ])
        ->assertRedirect(route('formateur.groupes.index'));

    expect($group->refresh()->temporary_password)->toBe('B');
});

it('searches only active registered co-trainers by email prefix', function () {
    $owner = createTrainerForCoTrainerTest('owner-search');
    $matchingTrainer = createTrainerForCoTrainerTest('aliasmatch');
    $excludedTrainer = createTrainerForCoTrainerTest('aliasexclude');
    $inactiveTrainer = createTrainerForCoTrainerTest('aliasinactive', false);

    $response = $this->actingAs($owner)->getJson(route('formateur.groupes.co-formateurs.search', [
        'q' => 'ali',
        'exclude' => [$excludedTrainer->id],
    ]));

    $response->assertOk();

    $ids = collect($response->json('items'))->pluck('id');

    expect($ids)->toContain($matchingTrainer->id);
    expect($ids)->not->toContain($owner->id);
    expect($ids)->not->toContain($excludedTrainer->id);
    expect($ids)->not->toContain($inactiveTrainer->id);
});

it('preserves existing modules and ignores stale co-trainer ids when updating a group', function () {
    $owner = createTrainerForCoTrainerTest('owner-stale-co');
    $inactiveCoTrainer = createTrainerForCoTrainerTest('inactive-stale-co', false);
    $module = createActiveModuleForTrainer($owner);
    $group = createSharedGroupForTrainer($owner, $module, $inactiveCoTrainer);
    $token = Str::random(40);

    $this
        ->withSession(['_token' => $token])
        ->actingAs($owner)
        ->put(route('formateur.groupes.update', $group->id), [
            '_token' => $token,
            'nom' => 'Groupe stale co-formateur modifie',
            'description' => 'Enregistrement sans champs modules generes par JS',
            'is_active' => '1',
            'co_formateurs' => [$inactiveCoTrainer->id],
            'stagiaires' => [],
        ])
        ->assertRedirect(route('formateur.groupes.index'));

    $this->assertDatabaseHas('group_module', [
        'group_id' => $group->id,
        'module_id' => $module->id,
        'position' => 1,
    ]);

    $this->assertDatabaseMissing('group_user', [
        'group_id' => $group->id,
        'user_id' => $inactiveCoTrainer->id,
        'role_in_group' => 'formateur',
    ]);
});

it('lets a co-trainer edit a shared group but not manage co-trainers or delete the group', function () {
    $owner = createTrainerForCoTrainerTest('owner-edit');
    $coTrainer = createTrainerForCoTrainerTest('co-edit');
    $otherTrainer = createTrainerForCoTrainerTest('other-edit');
    $module = createActiveModuleForTrainer($owner);
    $group = createSharedGroupForTrainer($owner, $module, $coTrainer);
    $token = Str::random(40);

    $this->actingAs($coTrainer)
        ->get(route('formateur.groupes.edit', $group->id))
        ->assertOk();

    $updateResponse = $this
        ->withSession(['_token' => $token])
        ->actingAs($coTrainer)
        ->put(route('formateur.groupes.update', $group->id), [
            '_token' => $token,
            'nom' => 'Groupe partage modifie',
            'description' => 'Description mise a jour',
            'is_active' => '1',
            'modules' => [$module->id],
            'module_positions' => [$module->id => 1],
            'co_formateurs' => [$otherTrainer->id],
            'stagiaires' => [],
        ]);

    $updateResponse->assertRedirect(route('formateur.groupes.index'));

    $group->refresh();

    expect($group->name)->toBe('Groupe partage modifie');

    $this->assertDatabaseHas('group_user', [
        'group_id' => $group->id,
        'user_id' => $coTrainer->id,
        'role_in_group' => 'formateur',
    ]);

    $this->assertDatabaseMissing('group_user', [
        'group_id' => $group->id,
        'user_id' => $otherTrainer->id,
        'role_in_group' => 'formateur',
    ]);

    $this
        ->withSession(['_token' => $token])
        ->actingAs($coTrainer)
        ->delete(route('formateur.groupes.destroy', $group->id), [
            '_token' => $token,
        ])
        ->assertForbidden();
});

it('deletes only the group links without deleting students or trainer-authored modules', function () {
    $owner = createTrainerForCoTrainerTest('delete-owner');
    $student = User::factory()->create([
        'prenom' => 'Stagiaire',
        'name' => 'Conserve',
        'username' => 'stagiaire_conserve_'.uniqid(),
        'email' => 'stagiaire.conserve.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => 'stagiaire',
        'status' => true,
        'password_changed_at' => now(),
    ]);
    $module = createActiveModuleForTrainer($owner);
    $module->forceFill(['is_trainer_authored' => true])->save();

    $group = Group::query()->create([
        'name' => 'Groupe suppression liens '.uniqid(),
        'description' => 'Groupe de test suppression',
        'is_active' => true,
        'temporary_password' => 'password123',
        'instructor_id' => $owner->id,
    ]);

    DB::table('group_user')->insert([
        'group_id' => $group->id,
        'user_id' => $student->id,
        'role_in_group' => 'stagiaire',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('group_module')->insert([
        'group_id' => $group->id,
        'module_id' => $module->id,
        'position' => 1,
    ]);

    $this
        ->actingAs($owner)
        ->delete(route('formateur.groupes.destroy', $group->id))
        ->assertRedirect(route('formateur.groupes.index'));

    $this->assertDatabaseMissing('groups', ['id' => $group->id]);
    $this->assertDatabaseMissing('group_user', [
        'group_id' => $group->id,
        'user_id' => $student->id,
    ]);
    $this->assertDatabaseMissing('group_module', [
        'group_id' => $group->id,
        'module_id' => $module->id,
    ]);
    $this->assertDatabaseHas('users', [
        'id' => $student->id,
        'role' => 'stagiaire',
    ]);
    $this->assertDatabaseHas('modules', [
        'id' => $module->id,
        'formateur_id' => $owner->id,
        'is_trainer_authored' => true,
        'deleted_at' => null,
    ]);
});
