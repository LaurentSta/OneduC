<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function makeFormateurStagiaireAssignmentUser(string $role, string $email, array $extra = []): User
{
    return User::query()->create(array_merge([
        'prenom' => 'Test',
        'name' => 'User',
        'username' => str_replace(['@', '.'], '_', $email),
        'email' => $email,
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
    ], $extra));
}

it('creates a stagiaire from the trainer area and immediately attaches it to the selected group', function () {
    $formateur = makeFormateurStagiaireAssignmentUser('formateur', 'formateur-stagiaire-create@example.test');
    $token = 'csrf-create-stagiaire';

    $group = Group::query()->create([
        'name' => 'Groupe accueil',
        'description' => 'Groupe principal',
        'instructor_id' => $formateur->id,
    ]);

    $response = $this->actingAs($formateur)
        ->withSession(['_token' => $token])
        ->post(route('formateur.stagiaires.store'), [
        '_token' => $token,
        'prenom' => 'Camille',
        'name' => 'Martin',
        'email' => 'camille.martin@example.test',
        'password' => 'secret1234',
        'password_confirmation' => 'secret1234',
        'group_id' => $group->id,
    ]);

    $response->assertRedirect(route('formateur.stagiaires.index'));

    $stagiaire = User::query()->where('email', 'camille.martin@example.test')->first();

    expect($stagiaire)->not->toBeNull();

    $this->assertDatabaseHas('group_user', [
        'group_id' => $group->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
    ]);
});

it('updates only the trainer managed group assignments when editing a stagiaire', function () {
    $formateur = makeFormateurStagiaireAssignmentUser('formateur', 'formateur-stagiaire-update@example.test');
    $otherFormateur = makeFormateurStagiaireAssignmentUser('formateur', 'other-formateur-stagiaire-update@example.test');
    $token = 'csrf-update-stagiaire';

    $groupA = Group::query()->create([
        'name' => 'Groupe A',
        'description' => 'Premier groupe',
        'instructor_id' => $formateur->id,
    ]);

    $groupB = Group::query()->create([
        'name' => 'Groupe B',
        'description' => 'Deuxième groupe',
        'instructor_id' => $formateur->id,
    ]);

    $foreignGroup = Group::query()->create([
        'name' => 'Groupe externe',
        'description' => 'Autre formateur',
        'instructor_id' => $otherFormateur->id,
    ]);

    $stagiaire = makeFormateurStagiaireAssignmentUser('stagiaire', 'stagiaire-update@example.test', [
        'formateur_id' => $formateur->id,
    ]);

    DB::table('group_user')->insert([
        [
            'group_id' => $groupA->id,
            'user_id' => $stagiaire->id,
            'role_in_group' => 'stagiaire',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'group_id' => $foreignGroup->id,
            'user_id' => $stagiaire->id,
            'role_in_group' => 'stagiaire',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->actingAs($formateur)
        ->withSession(['_token' => $token])
        ->put(route('formateur.stagiaires.update', $stagiaire), [
            '_token' => $token,
            'prenom' => 'Stagiaire',
            'name' => 'MisAJour',
            'email' => 'stagiaire-update@example.test',
            'group_ids' => [$groupB->id],
        ]);

    $response->assertRedirect(route('formateur.stagiaires.index'));

    $this->assertDatabaseMissing('group_user', [
        'group_id' => $groupA->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
    ]);

    $this->assertDatabaseHas('group_user', [
        'group_id' => $groupB->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
    ]);

    $this->assertDatabaseHas('group_user', [
        'group_id' => $foreignGroup->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
    ]);
});
