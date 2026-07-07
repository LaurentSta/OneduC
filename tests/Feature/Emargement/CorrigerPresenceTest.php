<?php

use App\Domains\Outils\Emargement\Actions\CreerSeance;
use App\Domains\Outils\Emargement\Actions\OuvrirSeance;
use App\Models\Group;
use App\Models\Seance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

const CORRIGER_TINY_PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

function createEmargementCorrigerUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' EmargementCorriger',
        'username' => $role.'_emargement_corriger_'.uniqid(),
        'email' => $role.'.emargement.corriger.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function createEmargementCorrigerGroup(User $formateur, array $students = []): Group
{
    $group = Group::query()->create([
        'name' => 'Groupe emargement corriger '.uniqid(),
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

function openCorrigerSeanceFor(Group $group, User $formateur): Seance
{
    $seance = (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);

    return (new OuvrirSeance)->execute($seance);
}

it('lets the formateur mark a stagiaire absent with a motive', function () {
    $formateur = createEmargementCorrigerUser('formateur');
    $stagiaire = createEmargementCorrigerUser('stagiaire');
    $group = createEmargementCorrigerGroup($formateur, [$stagiaire]);
    $seance = openCorrigerSeanceFor($group, $formateur);
    $presence = $seance->presences()->where('user_id', $stagiaire->id)->firstOrFail();

    $response = $this->actingAs($formateur)->post(
        route('formateur.groupes.emargement.presences.corriger', [$group->id, $seance->id, $presence->id]),
        ['statut' => 'absent', 'motif_absence' => 'Retard transport']
    );

    $response->assertRedirect(route('formateur.groupes.emargement.show', [$group->id, $seance->id]));

    $presence->refresh();
    expect($presence->statut)->toBe('absent');
    expect($presence->motif_absence)->toBe('Retard transport');
    expect($presence->updated_by)->toBe($formateur->id);
});

it('lets the formateur sign on behalf of a stagiaire', function () {
    Storage::fake('local');

    $formateur = createEmargementCorrigerUser('formateur');
    $stagiaire = createEmargementCorrigerUser('stagiaire');
    $group = createEmargementCorrigerGroup($formateur, [$stagiaire]);
    $seance = openCorrigerSeanceFor($group, $formateur);
    $presence = $seance->presences()->where('user_id', $stagiaire->id)->firstOrFail();

    $response = $this->actingAs($formateur)->post(
        route('formateur.groupes.emargement.presences.corriger', [$group->id, $seance->id, $presence->id]),
        ['statut' => 'present', 'signature' => 'data:image/png;base64,'.CORRIGER_TINY_PNG_BASE64]
    );

    $response->assertRedirect();

    $presence->refresh();
    expect($presence->statut)->toBe('present');
    expect($presence->signature_type)->toBe('formateur');
    expect($presence->getFirstMedia('signature'))->not->toBeNull();
});

it('requires a signature to mark present via correction', function () {
    $formateur = createEmargementCorrigerUser('formateur');
    $stagiaire = createEmargementCorrigerUser('stagiaire');
    $group = createEmargementCorrigerGroup($formateur, [$stagiaire]);
    $seance = openCorrigerSeanceFor($group, $formateur);
    $presence = $seance->presences()->where('user_id', $stagiaire->id)->firstOrFail();

    $response = $this->actingAs($formateur)->post(
        route('formateur.groupes.emargement.presences.corriger', [$group->id, $seance->id, $presence->id]),
        ['statut' => 'present']
    );

    $response->assertSessionHasErrors('signature');
});

it('forbids a formateur outside the group from correcting a presence', function () {
    $owner = createEmargementCorrigerUser('formateur');
    $intrus = createEmargementCorrigerUser('formateur');
    $stagiaire = createEmargementCorrigerUser('stagiaire');
    $group = createEmargementCorrigerGroup($owner, [$stagiaire]);
    $seance = openCorrigerSeanceFor($group, $owner);
    $presence = $seance->presences()->where('user_id', $stagiaire->id)->firstOrFail();

    $response = $this->actingAs($intrus)->post(
        route('formateur.groupes.emargement.presences.corriger', [$group->id, $seance->id, $presence->id]),
        ['statut' => 'absent']
    );

    $response->assertNotFound();
});
