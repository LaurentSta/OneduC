<?php

use App\Models\Group;
use App\Models\User;
use App\Models\WordCloud;

function createWordCloudFormateur(): User
{
    return User::factory()->create(['role' => 'formateur']);
}

function createWordCloudGroup(User $formateur): Group
{
    return Group::query()->create([
        'name' => 'Groupe wordcloud '.uniqid(),
        'description' => 'Groupe de test pour le nuage de mots',
        'instructor_id' => $formateur->id,
    ]);
}

function attachWordCloudStudent(Group $group): User
{
    $student = User::factory()->create([
        'role' => 'stagiaire',
        'password_changed_at' => now(),
    ]);

    $group->students()->attach($student->id, ['role_in_group' => 'stagiaire']);

    return $student;
}

it('lets a trainer launch a word cloud for their own group and see live results', function () {
    $formateur = createWordCloudFormateur();
    $group = createWordCloudGroup($formateur);
    $stagiaire = attachWordCloudStudent($group);

    $storeResponse = $this->actingAs($formateur)
        ->post(route('formateur.nuages.store'), [
            'group_id' => $group->id,
            'title' => 'Nuage de test',
            'questions' => ['Quel mot decrit la formation ?'],
        ]);

    $wordCloud = WordCloud::query()->firstOrFail();

    $storeResponse->assertRedirect(route('formateur.nuages.live', $wordCloud));
    expect($wordCloud->access_code)->not->toBeEmpty();
    expect($wordCloud->is_active)->toBeTrue();

    // Un participant repond via le lien de nuage, en tant que stagiaire du groupe.
    $this->actingAs($stagiaire)
        ->post(route('wordcloud.submit', ['code' => $wordCloud->access_code]), [
            'answer' => 'collaboratif',
            'question_index' => 0,
        ])->assertRedirect();

    $liveDataResponse = $this->actingAs($formateur)
        ->getJson(route('formateur.nuages.live.data', $wordCloud));

    $liveDataResponse->assertOk();
    $liveDataResponse->assertJsonPath('total_entries', 1);
    $liveDataResponse->assertJsonPath('words.0.word', 'collaboratif');
});

it('blocks a trainer from viewing the live results of another trainer word cloud', function () {
    $owner = createWordCloudFormateur();
    $intruder = createWordCloudFormateur();
    $group = createWordCloudGroup($owner);

    $wordCloud = WordCloud::query()->create([
        'group_id' => $group->id,
        'title' => 'Nuage prive',
        'question' => 'Question ?',
        'questions' => ['Question ?'],
        'access_code' => 'PRIVE1',
        'is_active' => true,
        'opened_at' => now(),
    ]);

    $this->actingAs($intruder)
        ->get(route('formateur.nuages.live', $wordCloud))
        ->assertForbidden();

    $this->actingAs($intruder)
        ->getJson(route('formateur.nuages.live.data', $wordCloud))
        ->assertForbidden();
});
