<?php

use App\Models\Group;
use App\Models\QuestionWall;
use App\Models\User;

function createQuestionWallFormateur(): User
{
    return User::factory()->create(['role' => 'formateur']);
}

function createQuestionWallGroup(User $formateur): Group
{
    return Group::query()->create([
        'name' => 'Groupe mur questions '.uniqid(),
        'description' => 'Groupe de test pour le mur de questions',
        'instructor_id' => $formateur->id,
    ]);
}

function attachQuestionWallStudent(Group $group, array $attributes = []): User
{
    $student = User::factory()->create(array_merge(['role' => 'stagiaire'], $attributes));
    $group->students()->attach($student->id, ['role_in_group' => 'stagiaire']);

    return $student;
}

it('lets a trainer launch a question wall and see the submitted questions', function () {
    $formateur = createQuestionWallFormateur();
    $group = createQuestionWallGroup($formateur);
    $stagiaire = attachQuestionWallStudent($group);

    $storeResponse = $this->actingAs($formateur)
        ->post(route('formateur.questions.store'), [
            'group_id' => $group->id,
            'title' => 'Mur de test',
        ]);

    $wall = QuestionWall::query()->firstOrFail();
    $storeResponse->assertRedirect(route('formateur.questions.show', $wall));

    $this->actingAs($stagiaire)
        ->post(route('questions.submit', ['code' => $wall->access_code]), [
            'body' => 'Comment fonctionne le SCORM ?',
        ])
        ->assertRedirect();

    $stateResponse = $this->actingAs($formateur)
        ->getJson(route('formateur.questions.state', $wall));

    $stateResponse->assertOk();
    $stateResponse->assertJsonCount(1, 'questions');
    $stateResponse->assertJsonPath('questions.0.body', 'Comment fonctionne le SCORM ?');
});

it('blocks a stagiaire outside the group from posting a question', function () {
    $formateur = createQuestionWallFormateur();
    $group = createQuestionWallGroup($formateur);
    $intruder = User::factory()->create(['role' => 'stagiaire']);

    $wall = QuestionWall::query()->create([
        'formateur_id' => $formateur->id,
        'group_id' => $group->id,
        'title' => 'Mur prive',
        'access_code' => 'MUR001',
        'is_active' => true,
    ]);

    $this->actingAs($intruder)
        ->post(route('questions.submit', ['code' => $wall->access_code]), [
            'body' => 'Question intruse',
        ])
        ->assertForbidden();
});

it('blocks a trainer from viewing the state of another trainer question wall', function () {
    $owner = createQuestionWallFormateur();
    $intruder = createQuestionWallFormateur();
    $group = createQuestionWallGroup($owner);

    $wall = QuestionWall::query()->create([
        'formateur_id' => $owner->id,
        'group_id' => $group->id,
        'title' => 'Mur prive',
        'access_code' => 'MUR002',
        'is_active' => true,
    ]);

    $this->actingAs($intruder)
        ->getJson(route('formateur.questions.state', $wall))
        ->assertForbidden();
});
