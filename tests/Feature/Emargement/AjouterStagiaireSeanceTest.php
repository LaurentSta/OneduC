<?php

use App\Domains\Outils\Emargement\Actions\AjouterStagiaireSeance;
use App\Domains\Outils\Emargement\Actions\CreerSeance;
use App\Domains\Outils\Emargement\Actions\OuvrirSeance;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

const AJOUTER_TINY_PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

function createAjouterStagiaireUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' AjouterStagiaire',
        'username' => $role.'_ajouter_stagiaire_'.uniqid(),
        'email' => $role.'.ajouter.stagiaire.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function addStagiaireToGroup(Group $group, User $stagiaire): void
{
    DB::table('group_user')->insert([
        'group_id' => $group->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('adds a late-joining group member to an existing seance', function () {
    $formateur = createAjouterStagiaireUser('formateur');
    $original = createAjouterStagiaireUser('stagiaire');
    $latecomer = createAjouterStagiaireUser('stagiaire');
    $group = Group::query()->create([
        'name' => 'Groupe ajouter stagiaire '.uniqid(),
        'description' => 'Groupe de test',
        'temporary_password' => 'temp-password',
        'instructor_id' => $formateur->id,
    ]);
    addStagiaireToGroup($group, $original);
    $seance = (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);

    // Latecomer joins the group after the seance's roster was already frozen.
    addStagiaireToGroup($group, $latecomer);

    expect($seance->presences()->where('user_id', $latecomer->id)->exists())->toBeFalse();

    $this->actingAs($formateur)
        ->post(route('formateur.groupes.emargement.presences.ajouter', [$group->id, $seance->id]), [
            'user_id' => $latecomer->id,
        ])
        ->assertRedirect(route('formateur.groupes.emargement.show', [$group->id, $seance->id]));

    $presence = $seance->presences()->where('user_id', $latecomer->id)->first();
    expect($presence)->not->toBeNull();
    expect($presence->statut)->toBe('en_attente');
});

it('is idempotent when the action is called twice for the same stagiaire', function () {
    $formateur = createAjouterStagiaireUser('formateur');
    $stagiaire = createAjouterStagiaireUser('stagiaire');
    $group = Group::query()->create([
        'name' => 'Groupe ajouter idempotent '.uniqid(),
        'description' => 'Groupe de test',
        'temporary_password' => 'temp-password',
        'instructor_id' => $formateur->id,
    ]);
    $seance = (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);
    addStagiaireToGroup($group, $stagiaire);

    (new AjouterStagiaireSeance)->execute($seance, $stagiaire);
    (new AjouterStagiaireSeance)->execute($seance, $stagiaire);

    expect($seance->presences()->where('user_id', $stagiaire->id)->count())->toBe(1);
});

it('rejects a user who is not a member of the group', function () {
    $formateur = createAjouterStagiaireUser('formateur');
    $outsider = createAjouterStagiaireUser('stagiaire');
    $group = Group::query()->create([
        'name' => 'Groupe ajouter rejet '.uniqid(),
        'description' => 'Groupe de test',
        'temporary_password' => 'temp-password',
        'instructor_id' => $formateur->id,
    ]);
    $seance = (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);

    $this->actingAs($formateur)
        ->post(route('formateur.groupes.emargement.presences.ajouter', [$group->id, $seance->id]), [
            'user_id' => $outsider->id,
        ])
        ->assertSessionHasErrors('user_id');

    expect($seance->presences()->where('user_id', $outsider->id)->exists())->toBeFalse();
});

it('forbids a formateur outside the group from adding a stagiaire', function () {
    $owner = createAjouterStagiaireUser('formateur');
    $intrus = createAjouterStagiaireUser('formateur');
    $stagiaire = createAjouterStagiaireUser('stagiaire');
    $group = Group::query()->create([
        'name' => 'Groupe ajouter intrus '.uniqid(),
        'description' => 'Groupe de test',
        'temporary_password' => 'temp-password',
        'instructor_id' => $owner->id,
    ]);
    $seance = (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $owner);
    addStagiaireToGroup($group, $stagiaire);

    $this->actingAs($intrus)
        ->post(route('formateur.groupes.emargement.presences.ajouter', [$group->id, $seance->id]), [
            'user_id' => $stagiaire->id,
        ])
        ->assertNotFound();
});

it('shows the late-entry block only when a group member is missing from the seance', function () {
    $formateur = createAjouterStagiaireUser('formateur');
    $original = createAjouterStagiaireUser('stagiaire');
    $latecomer = createAjouterStagiaireUser('stagiaire');
    $group = Group::query()->create([
        'name' => 'Groupe ajouter affichage '.uniqid(),
        'description' => 'Groupe de test',
        'temporary_password' => 'temp-password',
        'instructor_id' => $formateur->id,
    ]);
    addStagiaireToGroup($group, $original);
    $seance = (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);

    $this->actingAs($formateur)
        ->get(route('formateur.groupes.emargement.show', [$group->id, $seance->id]))
        ->assertDontSee('Entrée tardive');

    addStagiaireToGroup($group, $latecomer);

    $this->actingAs($formateur)
        ->get(route('formateur.groupes.emargement.show', [$group->id, $seance->id]))
        ->assertSee('Entrée tardive')
        ->assertSee($latecomer->prenom);
});

it('lets a late-added stagiaire sign the seance once added', function () {
    Storage::fake('local');

    $formateur = createAjouterStagiaireUser('formateur');
    $original = createAjouterStagiaireUser('stagiaire');
    $latecomer = createAjouterStagiaireUser('stagiaire');
    $group = Group::query()->create([
        'name' => 'Groupe ajouter puis signer '.uniqid(),
        'description' => 'Groupe de test',
        'temporary_password' => 'temp-password',
        'instructor_id' => $formateur->id,
    ]);
    addStagiaireToGroup($group, $original);
    $seance = (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);
    (new OuvrirSeance)->execute($seance);
    addStagiaireToGroup($group, $latecomer);

    $this->actingAs($formateur)->post(
        route('formateur.groupes.emargement.presences.ajouter', [$group->id, $seance->id]),
        ['user_id' => $latecomer->id]
    );

    $response = $this->actingAs($latecomer)->post(
        route('stagiaire.emargement.signer', $group->id),
        ['signature' => 'data:image/png;base64,'.AJOUTER_TINY_PNG_BASE64]
    );

    $response->assertRedirect(route('stagiaire.emargement.show', $group->id));
    $response->assertSessionMissing('error');

    $presence = $seance->presences()->where('user_id', $latecomer->id)->firstOrFail();
    expect($presence->statut)->toBe('present');
});
