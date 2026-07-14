<?php

use App\Models\Group;
use App\Models\GroupTimer;
use App\Models\User;

function createTimerFormateur(): User
{
    return User::factory()->create(['role' => 'formateur']);
}

function createTimerGroup(User $formateur, array $attributes = []): Group
{
    return Group::query()->create(array_merge([
        'name' => 'Groupe minuteur '.uniqid(),
        'description' => 'Groupe de test pour le minuteur',
        'instructor_id' => $formateur->id,
    ], $attributes));
}

function attachTimerStudent(Group $group, array $attributes = []): User
{
    $student = User::factory()->create(array_merge([
        'role' => 'stagiaire',
        'password_changed_at' => now(),
    ], $attributes));

    $group->students()->attach($student->id, ['role_in_group' => 'stagiaire']);

    return $student;
}

it('lets a trainer configure and start a timer that the stagiaire sees in real time', function () {
    $formateur = createTimerFormateur();
    $group = createTimerGroup($formateur);
    $stagiaire = attachTimerStudent($group);

    $configureResponse = $this->actingAs($formateur)
        ->postJson(route('formateur.groupes.timer.configure', $group), [
            'duration_seconds' => 600,
            'label' => 'Exercice pratique',
        ]);

    $configureResponse->assertOk();
    $configureResponse->assertJsonPath('duration_seconds', 600);
    $configureResponse->assertJsonPath('label', 'Exercice pratique');

    $this->actingAs($formateur)
        ->postJson(route('formateur.groupes.timer.start', $group))
        ->assertOk()
        ->assertJsonPath('status', 'running');

    $stagiaireStatus = $this->actingAs($stagiaire)
        ->getJson(route('stagiaire.timer.status', ['group' => $group->id]));

    $stagiaireStatus->assertOk();
    $stagiaireStatus->assertJsonPath('status', 'running');
    $stagiaireStatus->assertJsonPath('label', 'Exercice pratique');
});

it('denies a trainer without access to the group', function () {
    $owner = createTimerFormateur();
    $intruder = createTimerFormateur();
    $group = createTimerGroup($owner);

    $this->actingAs($intruder)
        ->getJson(route('formateur.groupes.timer.status', $group))
        ->assertNotFound();
});

it('denies a stagiaire outside the group', function () {
    $formateur = createTimerFormateur();
    $group = createTimerGroup($formateur);
    $intruder = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $this->actingAs($intruder)
        ->get(route('stagiaire.timer.show', ['group' => $group->id]))
        ->assertNotFound();
});

it('denies a stagiaire when the group is inactive', function () {
    $formateur = createTimerFormateur();
    $group = createTimerGroup($formateur, ['is_active' => false]);
    $stagiaire = attachTimerStudent($group);

    GroupTimer::ensureForGroup($group, $formateur);

    $this->actingAs($stagiaire)
        ->get(route('stagiaire.timer.show', ['group' => $group->id]))
        ->assertNotFound();
});
