<?php

use App\Domains\Outils\Emargement\Actions\CreerSeance;
use App\Domains\Outils\Emargement\Actions\OuvrirSeance;
use App\Domains\Outils\Emargement\Actions\SignerPresence;
use App\Domains\Outils\Emargement\Support\SignatureImage;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

const BELL_TINY_PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

function createBellEmargementUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' BellEmargement',
        'username' => $role.'_bell_emargement_'.uniqid(),
        'email' => $role.'.bell.emargement.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function createBellEmargementGroup(User $formateur, array $students, string $name): Group
{
    $group = Group::query()->create([
        'name' => $name,
        'description' => 'Groupe de test',
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

it('reports has_open_seance true when a seance is open and not yet signed', function () {
    $formateur = createBellEmargementUser('formateur');
    $stagiaire = createBellEmargementUser('stagiaire');
    $group = createBellEmargementGroup($formateur, [$stagiaire], 'Groupe cloche '.uniqid());
    $seance = (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);
    (new OuvrirSeance)->execute($seance);

    $response = $this->actingAs($stagiaire)->getJson(route('stagiaire.emargement.notification-status'));

    $response->assertOk()
        ->assertJsonPath('has_open_seance', true)
        ->assertJsonPath('group_name', $group->name)
        ->assertJsonPath('join_url', route('stagiaire.emargement.show', $group->id));
});

it('reports has_open_seance false once the stagiaire has signed', function () {
    Storage::fake('local');

    $formateur = createBellEmargementUser('formateur');
    $stagiaire = createBellEmargementUser('stagiaire');
    $group = createBellEmargementGroup($formateur, [$stagiaire], 'Groupe cloche signee '.uniqid());
    $seance = (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);
    $seance = (new OuvrirSeance)->execute($seance);
    $presence = $seance->presences()->where('user_id', $stagiaire->id)->firstOrFail();
    (new SignerPresence(new SignatureImage))->execute($presence, $stagiaire, 'data:image/png;base64,'.BELL_TINY_PNG_BASE64);

    $response = $this->actingAs($stagiaire)->getJson(route('stagiaire.emargement.notification-status'));

    $response->assertOk()->assertJsonPath('has_open_seance', false);
});

it('reports has_open_seance false when no seance is open', function () {
    $formateur = createBellEmargementUser('formateur');
    $stagiaire = createBellEmargementUser('stagiaire');
    $group = createBellEmargementGroup($formateur, [$stagiaire], 'Groupe cloche fermee '.uniqid());
    (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);

    $response = $this->actingAs($stagiaire)->getJson(route('stagiaire.emargement.notification-status'));

    $response->assertOk()->assertJsonPath('has_open_seance', false);
});

it('shows the emargement bell item as visible when a seance is open', function () {
    $formateur = createBellEmargementUser('formateur');
    $stagiaire = createBellEmargementUser('stagiaire');
    $group = createBellEmargementGroup($formateur, [$stagiaire], 'Groupe cloche visible '.uniqid());
    $seance = (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);
    (new OuvrirSeance)->execute($seance);

    $response = $this->actingAs($stagiaire)->get(route('stagiaire.outils'));
    $response->assertOk();

    preg_match('/data-emargement-item\s+class="([^"]*)"/', $response->getContent(), $matches);
    expect($matches[1] ?? null)->not->toBeNull();
    expect($matches[1])->not->toContain('hidden');
});

it('keeps the emargement bell item hidden when no seance is open', function () {
    $formateur = createBellEmargementUser('formateur');
    $stagiaire = createBellEmargementUser('stagiaire');
    createBellEmargementGroup($formateur, [$stagiaire], 'Groupe cloche cachee '.uniqid());

    $response = $this->actingAs($stagiaire)->get(route('stagiaire.outils'));
    $response->assertOk();

    preg_match('/data-emargement-item\s+class="([^"]*)"/', $response->getContent(), $matches);
    expect($matches[1] ?? null)->not->toBeNull();
    expect($matches[1])->toContain('hidden');
});
