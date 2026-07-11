<?php

use App\Domains\Outils\Emargement\Actions\CreerSeance;
use App\Domains\Outils\Emargement\Actions\OuvrirSeance;
use App\Models\Group;
use App\Models\Seance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

const TINY_PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

function createEmargementSignUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' EmargementSign',
        'username' => $role.'_emargement_sign_'.uniqid(),
        'email' => $role.'.emargement.sign.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function createEmargementSignGroup(User $formateur, array $students = []): Group
{
    $group = Group::query()->create([
        'name' => 'Groupe emargement sign '.uniqid(),
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

function openSeanceFor(Group $group, User $formateur): Seance
{
    $seance = (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);

    return (new OuvrirSeance)->execute($seance);
}

it('allows a group member to sign an open seance', function () {
    Storage::fake('local');

    $formateur = createEmargementSignUser('formateur');
    $stagiaire = createEmargementSignUser('stagiaire');
    $group = createEmargementSignGroup($formateur, [$stagiaire]);
    $seance = openSeanceFor($group, $formateur);

    $response = $this->actingAs($stagiaire)->postJson(
        route('stagiaire.emargement.signer', $group->id),
        ['signature' => 'data:image/png;base64,'.TINY_PNG_BASE64]
    );

    $response->assertRedirect(route('stagiaire.emargement.show', $group->id));

    $presence = $seance->presences()->where('user_id', $stagiaire->id)->firstOrFail();
    expect($presence->statut)->toBe('present');
    expect($presence->signature_type)->toBe('auto');
    expect($presence->getFirstMedia('signature'))->not->toBeNull();
});

it('denies signing to a student outside the group', function () {
    $formateur = createEmargementSignUser('formateur');
    $stagiaire = createEmargementSignUser('stagiaire');
    $intrus = createEmargementSignUser('stagiaire');
    $group = createEmargementSignGroup($formateur, [$stagiaire]);
    openSeanceFor($group, $formateur);

    $response = $this->actingAs($intrus)->get(route('stagiaire.emargement.show', $group->id));

    $response->assertNotFound();
});

it('refuses signing when no seance is open', function () {
    $formateur = createEmargementSignUser('formateur');
    $stagiaire = createEmargementSignUser('stagiaire');
    $group = createEmargementSignGroup($formateur, [$stagiaire]);
    // seance created but not opened
    (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);

    $response = $this->actingAs($stagiaire)->postJson(
        route('stagiaire.emargement.signer', $group->id),
        ['signature' => 'data:image/png;base64,'.TINY_PNG_BASE64]
    );

    $response->assertNotFound();
});

it('refuses signing twice', function () {
    Storage::fake('local');

    $formateur = createEmargementSignUser('formateur');
    $stagiaire = createEmargementSignUser('stagiaire');
    $group = createEmargementSignGroup($formateur, [$stagiaire]);
    $seance = openSeanceFor($group, $formateur);

    $this->actingAs($stagiaire)->postJson(
        route('stagiaire.emargement.signer', $group->id),
        ['signature' => 'data:image/png;base64,'.TINY_PNG_BASE64]
    )->assertRedirect();

    $response = $this->actingAs($stagiaire)->postJson(
        route('stagiaire.emargement.signer', $group->id),
        ['signature' => 'data:image/png;base64,'.TINY_PNG_BASE64]
    );

    $response->assertStatus(422)->assertJsonValidationErrors('signature');
});

it('renders the signing page with the signature pad for a group member', function () {
    $formateur = createEmargementSignUser('formateur');
    $stagiaire = createEmargementSignUser('stagiaire');
    $group = createEmargementSignGroup($formateur, [$stagiaire]);
    openSeanceFor($group, $formateur);

    $response = $this->actingAs($stagiaire)->get(route('stagiaire.emargement.show', $group->id));

    $response->assertOk();
    $response->assertSee('signaturePadComponent', false);
});

it('shows a friendly message instead of crashing when the stagiaire joined the group after the seance was created', function () {
    $formateur = createEmargementSignUser('formateur');
    $stagiaire = createEmargementSignUser('stagiaire');
    $latecomer = createEmargementSignUser('stagiaire');
    $group = createEmargementSignGroup($formateur, [$stagiaire]);
    openSeanceFor($group, $formateur);

    // Latecomer joins the group after the seance (and its frozen roster) already exist.
    DB::table('group_user')->insert([
        'group_id' => $group->id,
        'user_id' => $latecomer->id,
        'role_in_group' => 'stagiaire',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $showResponse = $this->actingAs($latecomer)->get(route('stagiaire.emargement.show', $group->id));
    $showResponse->assertOk();
    $showResponse->assertSee('pas inscrit', false);

    $signResponse = $this->actingAs($latecomer)->post(
        route('stagiaire.emargement.signer', $group->id),
        ['signature' => 'data:image/png;base64,'.TINY_PNG_BASE64]
    );

    $signResponse->assertRedirect(route('stagiaire.emargement.show', $group->id));
    $signResponse->assertSessionHas('error');
});

it('renders an empty state when no seance is open', function () {
    $formateur = createEmargementSignUser('formateur');
    $stagiaire = createEmargementSignUser('stagiaire');
    $group = createEmargementSignGroup($formateur, [$stagiaire]);

    $response = $this->actingAs($stagiaire)->get(route('stagiaire.emargement.show', $group->id));

    $response->assertOk();
    $response->assertSee('Aucune séance', false);
});
