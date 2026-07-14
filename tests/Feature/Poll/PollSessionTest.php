<?php

use App\Models\Group;
use App\Models\PollSession;
use App\Models\User;

function createPollFormateur(): User
{
    return User::factory()->create(['role' => 'formateur']);
}

function createPollGroup(User $formateur): Group
{
    return Group::query()->create([
        'name' => 'Groupe sondage '.uniqid(),
        'description' => 'Groupe de test pour le sondage',
        'instructor_id' => $formateur->id,
    ]);
}

function attachPollStudent(Group $group, array $attributes = []): User
{
    $student = User::factory()->create(array_merge(['role' => 'stagiaire'], $attributes));
    $group->students()->attach($student->id, ['role_in_group' => 'stagiaire']);

    return $student;
}

it('lets a trainer launch a poll for their own group and see aggregated results', function () {
    $formateur = createPollFormateur();
    $group = createPollGroup($formateur);
    $stagiaire = attachPollStudent($group);

    $storeResponse = $this->actingAs($formateur)
        ->post(route('formateur.sondages.store'), [
            'group_id' => $group->id,
            'title' => 'Sondage de test',
            'questions' => [
                ['question' => 'Preferez-vous le matin ?', 'choices' => ['Oui', 'Non']],
            ],
        ]);

    $session = PollSession::query()->firstOrFail();
    $storeResponse->assertRedirect(route('formateur.sondages.show', $session));

    $this->actingAs($stagiaire)
        ->post(route('sondages.submit', ['code' => $session->access_code]), [
            'question_index' => 0,
            'choice_index' => 0,
        ])
        ->assertRedirect();

    $stateResponse = $this->actingAs($formateur)
        ->getJson(route('formateur.sondages.state', $session));

    $stateResponse->assertOk();
    $stateResponse->assertJsonPath('respondents_total', 1);
    $stateResponse->assertJsonPath('questions.0.choices.0.votes', 1);
});

it('blocks a stagiaire outside the group from submitting a poll answer', function () {
    $formateur = createPollFormateur();
    $group = createPollGroup($formateur);
    $intruder = User::factory()->create(['role' => 'stagiaire']);

    $session = PollSession::query()->create([
        'formateur_id' => $formateur->id,
        'group_id' => $group->id,
        'title' => 'Sondage prive',
        'questions' => [
            ['question' => 'Question ?', 'choices' => ['A', 'B']],
        ],
        'access_code' => 'SOND01',
        'is_active' => true,
        'opened_at' => now(),
    ]);

    $this->actingAs($intruder)
        ->post(route('sondages.submit', ['code' => $session->access_code]), [
            'question_index' => 0,
            'choice_index' => 0,
        ])
        ->assertForbidden();
});

it('blocks a trainer from viewing the state of another trainer poll', function () {
    $owner = createPollFormateur();
    $intruder = createPollFormateur();
    $group = createPollGroup($owner);

    $session = PollSession::query()->create([
        'formateur_id' => $owner->id,
        'group_id' => $group->id,
        'title' => 'Sondage prive',
        'questions' => [
            ['question' => 'Question ?', 'choices' => ['A', 'B']],
        ],
        'access_code' => 'SOND02',
        'is_active' => true,
        'opened_at' => now(),
    ]);

    $this->actingAs($intruder)
        ->getJson(route('formateur.sondages.state', $session))
        ->assertForbidden();
});
