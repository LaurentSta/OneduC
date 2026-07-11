<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function createPremiereConnexionUser(string $role, bool $passwordChanged): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' PremiereConnexion',
        'username' => $role.'_premiere_connexion_'.uniqid(),
        'email' => $role.'.premiere.connexion.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => $passwordChanged ? now() : null,
    ]);
}

function createPremiereConnexionGroup(User $formateur, User $stagiaire): Group
{
    $group = Group::query()->create([
        'name' => 'Groupe premiere connexion '.uniqid(),
        'description' => 'Groupe de test',
        'temporary_password' => 'temp-password',
        'instructor_id' => $formateur->id,
    ]);

    DB::table('group_user')->insert([
        'group_id' => $group->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $group;
}

it('remembers a scanned emargement link through the forced password change and returns to it', function () {
    $formateur = createPremiereConnexionUser('formateur', true);
    $stagiaire = createPremiereConnexionUser('stagiaire', false);
    $group = createPremiereConnexionGroup($formateur, $stagiaire);

    $this->actingAs($stagiaire)
        ->get(route('stagiaire.emargement.show', $group->id))
        ->assertRedirect(route('stagiaire.password.init'));

    $this->post(route('stagiaire.password.init.store'), [
        'password' => 'un-nouveau-mot-de-passe',
        'password_confirmation' => 'un-nouveau-mot-de-passe',
    ])->assertRedirect(route('stagiaire.emargement.show', $group->id));
});

it('still falls back to the dashboard when there was no prior intended destination', function () {
    $stagiaire = createPremiereConnexionUser('stagiaire', false);

    $this->actingAs($stagiaire)
        ->get(route('stagiaire.password.init'))
        ->assertOk();

    $this->post(route('stagiaire.password.init.store'), [
        'password' => 'un-nouveau-mot-de-passe',
        'password_confirmation' => 'un-nouveau-mot-de-passe',
    ])->assertRedirect(route('stagiaire.dashboard'));
});
