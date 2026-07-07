<?php

use App\Domains\Outils\Emargement\Actions\CreerSeance;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function createEmargementLiveUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' EmargementLive',
        'username' => $role.'_emargement_live_'.uniqid(),
        'email' => $role.'.emargement.live.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function createEmargementLiveGroup(User $formateur, array $students = []): Group
{
    $group = Group::query()->create([
        'name' => 'Groupe emargement live '.uniqid(),
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

it('lets the formateur open then close a seance, reflected in state endpoint', function () {
    $formateur = createEmargementLiveUser('formateur');
    $stagiaire = createEmargementLiveUser('stagiaire');
    $group = createEmargementLiveGroup($formateur, [$stagiaire]);
    $seance = (new CreerSeance)->execute($group, ['date' => now()->addDay()->toDateString(), 'creneau' => 'matin'], $formateur);

    $this->actingAs($formateur)
        ->post(route('formateur.groupes.emargement.ouvrir', [$group->id, $seance->id]))
        ->assertRedirect(route('formateur.groupes.emargement.show', [$group->id, $seance->id]));

    $seance->refresh();
    expect($seance->statut)->toBe('ouverte');
    expect($seance->opened_at)->not->toBeNull();

    $stateResponse = $this->actingAs($formateur)
        ->getJson(route('formateur.groupes.emargement.state', [$group->id, $seance->id]));

    $stateResponse->assertOk()
        ->assertJsonPath('statut', 'ouverte')
        ->assertJsonCount(1, 'presences')
        ->assertJsonPath('presences.0.statut', 'en_attente');

    $this->actingAs($formateur)
        ->post(route('formateur.groupes.emargement.fermer', [$group->id, $seance->id]))
        ->assertRedirect(route('formateur.groupes.emargement.show', [$group->id, $seance->id]));

    $seance->refresh();
    expect($seance->statut)->toBe('cloturee');
    expect($seance->closed_at)->not->toBeNull();
});

it('forbids a formateur outside the group from opening a seance', function () {
    $owner = createEmargementLiveUser('formateur');
    $intrus = createEmargementLiveUser('formateur');
    $group = createEmargementLiveGroup($owner);
    $seance = (new CreerSeance)->execute($group, ['date' => now()->addDay()->toDateString(), 'creneau' => 'matin'], $owner);

    $this->actingAs($intrus)
        ->post(route('formateur.groupes.emargement.ouvrir', [$group->id, $seance->id]))
        ->assertNotFound();
});

it('renders the pilotage page for the formateur', function () {
    $formateur = createEmargementLiveUser('formateur');
    $group = createEmargementLiveGroup($formateur);
    $seance = (new CreerSeance)->execute($group, ['date' => now()->addDay()->toDateString(), 'creneau' => 'matin'], $formateur);

    $this->actingAs($formateur)
        ->get(route('formateur.groupes.emargement.show', [$group->id, $seance->id]))
        ->assertOk()
        ->assertSee('Ouvrir la séance');
});
