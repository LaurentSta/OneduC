<?php

use App\Models\Group;
use App\Models\Seance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function createEmargementStoreUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' EmargementStore',
        'username' => $role.'_emargement_store_'.uniqid(),
        'email' => $role.'.emargement.store.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function createEmargementStoreGroup(User $formateur, array $students = []): Group
{
    $group = Group::query()->create([
        'name' => 'Groupe emargement store '.uniqid(),
        'description' => 'Groupe de test',
        'temporary_password' => 'temp-password',
        'instructor_id' => $formateur->id,
    ]);

    foreach ($students as $student) {
        DB::table('group_user')->insert([
            'group_id' => $group->id,
            'user_id' => $student->id,
            'role_in_group' => 'stagiaire',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return $group;
}

it('allows the group instructor to create a seance from the dedicated emargement view', function () {
    $formateur = createEmargementStoreUser('formateur');
    $stagiaire = createEmargementStoreUser('stagiaire');
    $group = createEmargementStoreGroup($formateur, [$stagiaire]);

    $response = $this->actingAs($formateur)->post(
        route('formateur.groupes.emargement.store', $group->id),
        ['date' => now()->addDay()->toDateString(), 'creneau' => 'matin']
    );

    $response->assertRedirect(route('formateur.emargement.index', ['group_id' => $group->id]));
    $this->assertDatabaseHas('seances', ['group_id' => $group->id, 'creneau' => 'matin']);

    $seance = Seance::where('group_id', $group->id)->firstOrFail();
    expect($seance->presences)->toHaveCount(1);
});

it('forbids a formateur outside the group from creating a seance', function () {
    $owner = createEmargementStoreUser('formateur');
    $intrus = createEmargementStoreUser('formateur');
    $group = createEmargementStoreGroup($owner);

    $response = $this->actingAs($intrus)->post(
        route('formateur.groupes.emargement.store', $group->id),
        ['date' => now()->addDay()->toDateString(), 'creneau' => 'matin']
    );

    $response->assertNotFound();
});

it('shows a validation error instead of a 500 when the seance already exists', function () {
    $formateur = createEmargementStoreUser('formateur');
    $group = createEmargementStoreGroup($formateur);
    $date = now()->addDay()->toDateString();

    $this->actingAs($formateur)->post(
        route('formateur.groupes.emargement.store', $group->id),
        ['date' => $date, 'creneau' => 'matin']
    )->assertRedirect();

    $response = $this->actingAs($formateur)->post(
        route('formateur.groupes.emargement.store', $group->id),
        ['date' => $date, 'creneau' => 'matin']
    );

    $response->assertSessionHasErrors('creneau');
    $this->assertDatabaseCount('seances', 1);
});

it('validates the creneau value', function () {
    $formateur = createEmargementStoreUser('formateur');
    $group = createEmargementStoreGroup($formateur);

    $response = $this->actingAs($formateur)->post(
        route('formateur.groupes.emargement.store', $group->id),
        ['date' => now()->addDay()->toDateString(), 'creneau' => 'nuit']
    );

    $response->assertSessionHasErrors('creneau');
});
