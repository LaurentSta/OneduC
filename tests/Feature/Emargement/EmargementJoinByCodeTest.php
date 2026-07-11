<?php

use App\Domains\Outils\Emargement\Actions\GenererCodeAccesGroupe;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function createEmargementJoinUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' EmargementJoin',
        'username' => $role.'_emargement_join_'.uniqid(),
        'email' => $role.'.emargement.join.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function createEmargementJoinGroup(User $formateur, array $students = [], array $attrs = []): Group
{
    $group = Group::query()->create(array_merge([
        'name' => 'Groupe emargement join '.uniqid(),
        'description' => 'Groupe de test',
        'temporary_password' => 'temp-password',
        'instructor_id' => $formateur->id,
        'emargement_enabled' => true,
    ], $attrs));

    foreach ($students as $student) {
        DB::table('group_user')->insert([
            'group_id' => $group->id,
            'user_id' => $student->id,
            'role_in_group' => 'stagiaire',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    (new GenererCodeAccesGroupe)->execute($group);

    return $group->fresh();
}

it('redirects a guest to login instead of showing the join form', function () {
    $this->get(route('emargement.join'))->assertRedirect(route('login'));
});

it('shows the join form to an authenticated user', function () {
    $stagiaire = createEmargementJoinUser('stagiaire');

    $this->actingAs($stagiaire)->get(route('emargement.join'))->assertOk();
});

it('resolves a submitted code to the uppercase join-by-code route', function () {
    $stagiaire = createEmargementJoinUser('stagiaire');

    $this->actingAs($stagiaire)
        ->post(route('emargement.resolve'), ['code' => 'ab12cd'])
        ->assertRedirect(route('emargement.join.code', ['code' => 'AB12CD']));
});

it('rejects a missing code on resolve', function () {
    $stagiaire = createEmargementJoinUser('stagiaire');

    $this->actingAs($stagiaire)
        ->post(route('emargement.resolve'), [])
        ->assertSessionHasErrors('code');
});

it('returns 404 for an unknown code', function () {
    $stagiaire = createEmargementJoinUser('stagiaire');

    $this->actingAs($stagiaire)
        ->get(route('emargement.join.code', ['code' => 'ZZZZZZ']))
        ->assertNotFound();
});

it('returns 404 when the code belongs to a group with emargement disabled', function () {
    $formateur = createEmargementJoinUser('formateur');
    $stagiaire = createEmargementJoinUser('stagiaire');
    $group = createEmargementJoinGroup($formateur, [$stagiaire], ['emargement_enabled' => false]);
    // The code column is nullable and only populated on activation; simulate a
    // pre-existing code from a group that has since been deactivated.
    $group->forceFill(['emargement_code' => 'DISABL'])->save();

    $this->actingAs($stagiaire)
        ->get(route('emargement.join.code', ['code' => 'DISABL']))
        ->assertNotFound();
});

it('redirects a member stagiaire to the signature page and lets them through', function () {
    $formateur = createEmargementJoinUser('formateur');
    $stagiaire = createEmargementJoinUser('stagiaire');
    $group = createEmargementJoinGroup($formateur, [$stagiaire]);

    $this->actingAs($stagiaire)
        ->get(route('emargement.join.code', ['code' => $group->emargement_code]))
        ->assertRedirect(route('stagiaire.emargement.show', $group->id));

    $this->actingAs($stagiaire)
        ->followingRedirects()
        ->get(route('emargement.join.code', ['code' => $group->emargement_code]))
        ->assertOk();
});

it('never grants access by the code alone: a non-member stagiaire still gets 404 after the redirect', function () {
    $formateur = createEmargementJoinUser('formateur');
    $outsider = createEmargementJoinUser('stagiaire');
    $group = createEmargementJoinGroup($formateur, []);

    $this->actingAs($outsider)
        ->followingRedirects()
        ->get(route('emargement.join.code', ['code' => $group->emargement_code]))
        ->assertNotFound();
});

it('redirects a guest visiting a join-by-code link to login rather than any content', function () {
    $formateur = createEmargementJoinUser('formateur');
    $stagiaire = createEmargementJoinUser('stagiaire');
    $group = createEmargementJoinGroup($formateur, [$stagiaire]);

    $this->get(route('emargement.join.code', ['code' => $group->emargement_code]))
        ->assertRedirect(route('login'));
});

it('logs out a wrong-role user who follows a join-by-code link with their own session', function () {
    $formateur = createEmargementJoinUser('formateur');
    $group = createEmargementJoinGroup($formateur, []);

    $this->actingAs($formateur)
        ->get(route('emargement.join.code', ['code' => $group->emargement_code]))
        ->assertRedirect(route('stagiaire.emargement.show', $group->id));

    $this->get(route('stagiaire.emargement.show', $group->id))
        ->assertRedirect('/connexion');

    $this->assertGuest();
});
