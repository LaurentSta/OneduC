<?php

use App\Domains\Outils\Emargement\Actions\CreerSeance;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function createEmargementIndexUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' EmargementIndex',
        'username' => $role.'_emargement_index_'.uniqid(),
        'email' => $role.'.emargement.index.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function createEmargementIndexGroup(User $formateur, array $students, string $name): Group
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

it('shows a group picker when no group is selected', function () {
    $formateur = createEmargementIndexUser('formateur');
    $stagiaire = createEmargementIndexUser('stagiaire');
    $group = createEmargementIndexGroup($formateur, [$stagiaire], 'Groupe picker '.uniqid());

    $response = $this->actingAs($formateur)->get(route('formateur.emargement.index'));

    $response->assertOk();
    $response->assertSeeText('Groupes');
    $response->assertSeeText($group->name);
});

it('shows the seances panel when an activated group is selected', function () {
    $formateur = createEmargementIndexUser('formateur');
    $stagiaire = createEmargementIndexUser('stagiaire');
    $group = createEmargementIndexGroup($formateur, [$stagiaire], 'Groupe selectionne '.uniqid());
    $group->update(['emargement_enabled' => true]);
    (new CreerSeance)->execute($group, ['date' => now()->addDay()->toDateString(), 'creneau' => 'matin'], $formateur);

    $response = $this->actingAs($formateur)->get(route('formateur.emargement.index', ['group_id' => $group->id]));

    $response->assertOk();
    $response->assertSee('Feuilles d', false);
    $response->assertSeeText('Piloter');
});

it('is disabled by default and prompts activation instead of showing the panel', function () {
    $formateur = createEmargementIndexUser('formateur');
    $group = createEmargementIndexGroup($formateur, [], 'Groupe non active '.uniqid());

    expect($group->fresh()->emargement_enabled)->toBeFalse();

    $response = $this->actingAs($formateur)->get(route('formateur.emargement.index', ['group_id' => $group->id]));

    $response->assertOk();
    $response->assertSeeText('Émargement non activé');
    $response->assertDontSeeText('Nouvelle séance');
});

it('lets the formateur activate emargement for a group', function () {
    $formateur = createEmargementIndexUser('formateur');
    $group = createEmargementIndexGroup($formateur, [], 'Groupe a activer '.uniqid());

    $response = $this->actingAs($formateur)->post(route('formateur.emargement.activer', $group->id));

    $response->assertRedirect(route('formateur.emargement.index', ['group_id' => $group->id]));
    expect($group->fresh()->emargement_enabled)->toBeTrue();
});

it('lets the formateur deactivate emargement for a group', function () {
    $formateur = createEmargementIndexUser('formateur');
    $group = createEmargementIndexGroup($formateur, [], 'Groupe a desactiver '.uniqid());
    $group->update(['emargement_enabled' => true]);

    $response = $this->actingAs($formateur)->post(route('formateur.emargement.desactiver', $group->id));

    $response->assertRedirect(route('formateur.emargement.index'));
    expect($group->fresh()->emargement_enabled)->toBeFalse();
});

it('forbids a formateur outside the group from activating emargement', function () {
    $owner = createEmargementIndexUser('formateur');
    $intrus = createEmargementIndexUser('formateur');
    $group = createEmargementIndexGroup($owner, [], 'Groupe protege '.uniqid());

    $response = $this->actingAs($intrus)->post(route('formateur.emargement.activer', $group->id));

    $response->assertNotFound();
    expect($group->fresh()->emargement_enabled)->toBeFalse();
});

it('ignores a group_id the formateur cannot access and falls back to the picker', function () {
    $formateur = createEmargementIndexUser('formateur');
    $ownGroup = createEmargementIndexGroup($formateur, [], 'Groupe propre '.uniqid());
    $stranger = createEmargementIndexUser('formateur');
    $foreignGroup = createEmargementIndexGroup($stranger, [], 'Groupe etranger '.uniqid());

    $response = $this->actingAs($formateur)->get(route('formateur.emargement.index', ['group_id' => $foreignGroup->id]));

    $response->assertOk();
    $response->assertSeeText('Groupes');
    $response->assertSeeText($ownGroup->name);
    $response->assertDontSeeText($foreignGroup->name);
});
