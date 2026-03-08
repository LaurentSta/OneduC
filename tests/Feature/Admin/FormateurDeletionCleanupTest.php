<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function makeUserForDeletionTest(string $role, string $email, array $extra = []): User
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

it('deletes trainer groups and stagiaires only linked to that trainer', function () {
    $admin = makeUserForDeletionTest('admin', 'admin-delete-formateur@example.test');
    $formateur = makeUserForDeletionTest('formateur', 'formateur-delete-a@example.test');

    $group = Group::query()->create([
        'name' => 'Groupe A',
        'description' => 'Groupe du formateur A',
        'instructor_id' => $formateur->id,
    ]);

    $stagiaire = makeUserForDeletionTest('stagiaire', 'stagiaire-delete-a@example.test', [
        'formateur_id' => $formateur->id,
    ]);

    DB::table('group_user')->insert([
        'group_id' => $group->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.formateurs.destroy', $formateur));

    $response->assertStatus(302);

    $this->assertSoftDeleted('users', ['id' => $formateur->id]);
    $this->assertDatabaseMissing('groups', ['id' => $group->id]);
    $this->assertSoftDeleted('users', ['id' => $stagiaire->id]);
});

it('keeps stagiaire if still linked to another trainer', function () {
    $admin = makeUserForDeletionTest('admin', 'admin-keep-stagiaire@example.test');
    $formateurA = makeUserForDeletionTest('formateur', 'formateur-delete-b@example.test');
    $formateurB = makeUserForDeletionTest('formateur', 'formateur-keep-b@example.test');

    $groupA = Group::query()->create([
        'name' => 'Groupe B-A',
        'description' => 'Groupe du formateur A',
        'instructor_id' => $formateurA->id,
    ]);

    $groupB = Group::query()->create([
        'name' => 'Groupe B-B',
        'description' => 'Groupe du formateur B',
        'instructor_id' => $formateurB->id,
    ]);

    $stagiaire = makeUserForDeletionTest('stagiaire', 'stagiaire-keep-b@example.test', [
        'formateur_id' => $formateurA->id,
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
            'group_id' => $groupB->id,
            'user_id' => $stagiaire->id,
            'role_in_group' => 'stagiaire',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.formateurs.destroy', $formateurA));

    $response->assertStatus(302);

    $this->assertSoftDeleted('users', ['id' => $formateurA->id]);
    $this->assertDatabaseMissing('groups', ['id' => $groupA->id]);
    $this->assertDatabaseHas('groups', ['id' => $groupB->id]);
    $this->assertDatabaseHas('group_user', [
        'group_id' => $groupB->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
    ]);
    $this->assertDatabaseHas('users', [
        'id' => $stagiaire->id,
        'deleted_at' => null,
        'formateur_id' => $formateurB->id,
    ]);
});
