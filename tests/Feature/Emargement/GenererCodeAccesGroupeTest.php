<?php

use App\Domains\Outils\Emargement\Actions\CreerSeance;
use App\Domains\Outils\Emargement\Actions\GenererCodeAccesGroupe;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function createGenererCodeUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' GenererCode',
        'username' => $role.'_generer_code_'.uniqid(),
        'email' => $role.'.generer.code.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function createGenererCodeGroup(User $formateur, array $attrs = []): Group
{
    return Group::query()->create(array_merge([
        'name' => 'Groupe generer code '.uniqid(),
        'description' => 'Groupe de test',
        'temporary_password' => 'temp-password',
        'instructor_id' => $formateur->id,
    ], $attrs));
}

it('generates a 6-character code for a group without one', function () {
    $formateur = createGenererCodeUser('formateur');
    $group = createGenererCodeGroup($formateur);

    expect($group->emargement_code)->toBeNull();

    $updated = (new GenererCodeAccesGroupe)->execute($group);

    expect($updated->emargement_code)->not->toBeNull();
    expect(strlen($updated->emargement_code))->toBe(6);
});

it('is idempotent and keeps the same code on repeated calls', function () {
    $formateur = createGenererCodeUser('formateur');
    $group = createGenererCodeGroup($formateur);

    $first = (new GenererCodeAccesGroupe)->execute($group)->emargement_code;
    $second = (new GenererCodeAccesGroupe)->execute($group->fresh())->emargement_code;

    expect($second)->toBe($first);
});

it('gives different codes to different groups', function () {
    $formateur = createGenererCodeUser('formateur');
    $groupA = createGenererCodeGroup($formateur);
    $groupB = createGenererCodeGroup($formateur);

    $codeA = (new GenererCodeAccesGroupe)->execute($groupA)->emargement_code;
    $codeB = (new GenererCodeAccesGroupe)->execute($groupB)->emargement_code;

    expect($codeA)->not->toBe($codeB);
});

it('generates a code when a formateur activates emargement for a group', function () {
    $formateur = createGenererCodeUser('formateur');
    $group = createGenererCodeGroup($formateur, ['emargement_enabled' => false]);

    $this->actingAs($formateur)
        ->post(route('formateur.emargement.activer', $group->id))
        ->assertRedirect();

    expect($group->fresh()->emargement_code)->not->toBeNull();
});

it('keeps the same code when a group is deactivated and reactivated', function () {
    $formateur = createGenererCodeUser('formateur');
    $group = createGenererCodeGroup($formateur, ['emargement_enabled' => false]);

    $this->actingAs($formateur)->post(route('formateur.emargement.activer', $group->id));
    $codeAfterFirstActivation = $group->fresh()->emargement_code;

    $this->actingAs($formateur)->post(route('formateur.emargement.desactiver', $group->id));
    $this->actingAs($formateur)->post(route('formateur.emargement.activer', $group->id));

    expect($group->fresh()->emargement_code)->toBe($codeAfterFirstActivation);
});

it('backfills the code on the pilotage screen for a group activated before this feature existed', function () {
    $formateur = createGenererCodeUser('formateur');
    $group = createGenererCodeGroup($formateur, ['emargement_enabled' => true]);
    $seance = (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);

    expect($group->emargement_code)->toBeNull();

    $this->actingAs($formateur)
        ->get(route('formateur.groupes.emargement.show', [$group->id, $seance->id]))
        ->assertOk();

    expect($group->fresh()->emargement_code)->not->toBeNull();
});
