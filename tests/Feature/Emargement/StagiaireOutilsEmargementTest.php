<?php

use App\Domains\Outils\Emargement\Actions\CreerSeance;
use App\Domains\Outils\Emargement\Actions\OuvrirSeance;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function createStagiaireOutilsUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' StagiaireOutils',
        'username' => $role.'_stagiaire_outils_'.uniqid(),
        'email' => $role.'.stagiaire.outils.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

it('shows a "Signer maintenant" link when a seance is open for the stagiaire group', function () {
    $formateur = createStagiaireOutilsUser('formateur');
    $stagiaire = createStagiaireOutilsUser('stagiaire');
    $group = Group::query()->create([
        'name' => 'Groupe stagiaire outils '.uniqid(),
        'description' => 'Groupe de test',
        'instructor_id' => $formateur->id,
    ]);
    DB::table('group_user')->insert([
        'group_id' => $group->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $seance = (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);
    (new OuvrirSeance)->execute($seance);

    $response = $this->actingAs($stagiaire)->get(route('stagiaire.outils'));

    $response->assertOk();
    $response->assertSeeText('Émargement');
    $response->assertSeeText('Signer maintenant');
});

it('does not show "Signer maintenant" when no seance is open', function () {
    $formateur = createStagiaireOutilsUser('formateur');
    $stagiaire = createStagiaireOutilsUser('stagiaire');
    $group = Group::query()->create([
        'name' => 'Groupe stagiaire outils ferme '.uniqid(),
        'description' => 'Groupe de test',
        'instructor_id' => $formateur->id,
    ]);
    DB::table('group_user')->insert([
        'group_id' => $group->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);

    $response = $this->actingAs($stagiaire)->get(route('stagiaire.outils'));

    $response->assertOk();
    $response->assertSeeText('Émargement');
    $response->assertDontSeeText('Signer maintenant');
});
