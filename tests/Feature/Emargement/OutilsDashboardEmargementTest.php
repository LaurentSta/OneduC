<?php

use App\Domains\Outils\Emargement\Actions\CreerSeance;
use App\Domains\Outils\Emargement\Actions\OuvrirSeance;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function createOutilsEmargementUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' OutilsEmargement',
        'username' => $role.'_outils_emargement_'.uniqid(),
        'email' => $role.'.outils.emargement.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

it('shows the emargement tile with the count of currently open seances', function () {
    $formateur = createOutilsEmargementUser('formateur');
    $group = Group::query()->create([
        'name' => 'Groupe outils emargement '.uniqid(),
        'description' => 'Groupe de test',
        'instructor_id' => $formateur->id,
    ]);
    $seance = (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);
    (new OuvrirSeance)->execute($seance);

    $response = $this->actingAs($formateur)->get(route('formateur.outils.index'));

    $response->assertOk();
    $response->assertSeeText('Émargement');

    $openSeancesCount = $response->viewData('openSeancesCount');
    expect($openSeancesCount)->toBe(1);
});
