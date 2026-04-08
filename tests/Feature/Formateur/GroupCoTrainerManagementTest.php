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
        'username' => $emailPrefix . '_' . uniqid(),
        'email' => $emailPrefix . '.' . uniqid() . '@example.test',
        'password' => Hash::make('password'),
        'role' => 'formateur',
        'status' => $active,
        'password_changed_at' => now(),
    ]);
}

function createActiveModuleForTrainer(User $formateur): Module
{
    $category = Category::query()->create([
        'category_name' => 'Categorie groupe ' . uniqid(),
        'category_slug' => 'categorie-groupe-' . uniqid(),
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous categorie groupe ' . uniqid(),
        'subcategory_slug' => 'sous-categorie-groupe-' . uniqid(),
    ]);

    return Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module groupe ' . uniqid(),
        'module_name' => 'Module groupe ' . uniqid(),
        'module_name_slug' => 'module-groupe-' . uniqid(),
        'status' => 1,
    ]);
}

function createSharedGroupForTrainer(User $owner, Module $module, User $coTrainer): Group
{
    $group = Group::query()->create([
        'name' => 'Groupe partage ' . uniqid(),
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
    $groupName = 'Groupe wizard co-formateur ' . uniqid();
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
