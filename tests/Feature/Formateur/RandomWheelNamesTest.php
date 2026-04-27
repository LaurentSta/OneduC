<?php

use App\Models\Group;
use App\Models\RandomWheelSession;
use App\Models\User;

function createRandomWheelFormateur(): User
{
    return User::factory()->create([
        'prenom' => 'Formatrice',
        'name' => 'Roue',
        'role' => 'formateur',
        'status' => true,
    ]);
}

function createRandomWheelGroup(User $formateur): Group
{
    return Group::query()->create([
        'name' => 'Groupe roue '.uniqid(),
        'description' => 'Groupe de test pour la roue',
        'instructor_id' => $formateur->id,
    ]);
}

function attachRandomWheelStudent(Group $group, array $attributes = []): User
{
    $student = User::factory()->create(array_merge([
        'prenom' => 'Camille',
        'name' => 'Martin',
        'role' => 'stagiaire',
        'status' => true,
    ], $attributes));

    $group->students()->attach($student->id, [
        'role_in_group' => 'stagiaire',
    ]);

    return $student;
}

it('creates random wheel entries with stagiaire first and last names', function () {
    $formateur = createRandomWheelFormateur();
    $group = createRandomWheelGroup($formateur);

    attachRandomWheelStudent($group, [
        'prenom' => 'Camille',
        'name' => 'Martin',
        'email' => 'camille.martin@example.test',
    ]);

    $response = $this->actingAs($formateur)
        ->post(route('formateur.roue.store'), [
            'group_id' => $group->id,
        ]);

    $session = RandomWheelSession::query()->firstOrFail();
    $names = collect($session->entries)->pluck('name')->all();

    $response->assertRedirect(route('formateur.roue.show', $session));
    expect($names)->toContain('Camille Martin');
    expect($names)->not->toContain('Camille M.');
});

it('refreshes existing random wheel sessions from the current group roster', function () {
    $formateur = createRandomWheelFormateur();
    $group = createRandomWheelGroup($formateur);
    $student = attachRandomWheelStudent($group, [
        'prenom' => 'Camille',
        'name' => 'Martin',
        'email' => 'camille.sync@example.test',
    ]);

    $session = RandomWheelSession::query()->create([
        'formateur_id' => $formateur->id,
        'group_id' => $group->id,
        'access_code' => 'ROUE42',
        'entries' => [
            ['id' => $student->id, 'name' => 'Camille M.'],
        ],
        'active_entry_ids' => [$student->id],
        'picks' => [$student->id],
        'current_pick_id' => $student->id,
        'spun_at' => now(),
    ]);

    $response = $this->actingAs($formateur)
        ->get(route('formateur.roue.show', $session));

    $response->assertOk();
    $response->assertSee('Camille Martin');
    $response->assertDontSee('Camille M.');

    $session->refresh();
    expect(collect($session->entries)->pluck('name')->all())->toBe(['Camille Martin']);
});

it('returns refreshed entries to the stagiaire live wheel state', function () {
    $formateur = createRandomWheelFormateur();
    $group = createRandomWheelGroup($formateur);
    $student = attachRandomWheelStudent($group, [
        'prenom' => 'Nadia',
        'name' => 'Bernard',
        'email' => 'nadia.bernard@example.test',
    ]);

    $session = RandomWheelSession::query()->create([
        'formateur_id' => $formateur->id,
        'group_id' => $group->id,
        'access_code' => 'NADIA1',
        'entries' => [
            ['id' => $student->id, 'name' => 'Nadia B.'],
        ],
        'active_entry_ids' => [$student->id],
        'picks' => [],
        'current_pick_id' => null,
    ]);

    $this->get(route('roue.state', $session->access_code))
        ->assertOk()
        ->assertJsonPath('entries.0.name', 'Nadia Bernard');
});
