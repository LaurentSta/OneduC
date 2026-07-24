<?php

use App\Models\Group;
use App\Models\GroupQuizSession;
use App\Models\QuizQuestionnaire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createGroupQuizFormateur(): User
{
    return User::factory()->create(['role' => 'formateur']);
}

function createGroupQuizGroup(User $formateur): Group
{
    return Group::query()->create([
        'name' => 'Groupe quiz '.uniqid(),
        'description' => 'Groupe de test',
        'instructor_id' => $formateur->id,
    ]);
}

function attachGroupQuizStudent(Group $group): User
{
    $student = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);
    $group->students()->attach($student->id, ['role_in_group' => 'stagiaire']);

    return $student;
}

it('crée un questionnaire avec les 4 types de questions puis lance un quiz pour un groupe, jusqu\'à la clôture', function () {
    $formateur = createGroupQuizFormateur();
    $group = createGroupQuizGroup($formateur);
    $student = attachGroupQuizStudent($group);

    $this->actingAs($formateur)
        ->post(route('formateur.questionnaires.store'), ['title' => 'Questionnaire de test'])
        ->assertRedirect();
    $questionnaire = QuizQuestionnaire::query()->firstOrFail();
    expect($questionnaire->formateur_id)->toBe($formateur->id);

    // Choix unique
    $this->actingAs($formateur)->post(route('formateur.questionnaires.questions.store', $questionnaire), [
        'type' => 'single',
        'question_text' => 'Combien font 2+2 ?',
        'options' => [
            ['text' => '3', 'is_correct' => '0'],
            ['text' => '4', 'is_correct' => '1'],
        ],
    ])->assertRedirect();

    // Vrai/Faux
    $this->actingAs($formateur)->post(route('formateur.questionnaires.questions.store', $questionnaire), [
        'type' => 'boolean',
        'question_text' => 'Paris est la capitale de la France.',
        'options' => [
            ['text' => 'Vrai', 'is_correct' => '1'],
            ['text' => 'Faux', 'is_correct' => '0'],
        ],
    ])->assertRedirect();

    // Choix multiples
    $this->actingAs($formateur)->post(route('formateur.questionnaires.questions.store', $questionnaire), [
        'type' => 'multiple',
        'question_text' => 'Sélectionnez les nombres pairs.',
        'options' => [
            ['text' => '2', 'is_correct' => '1'],
            ['text' => '3', 'is_correct' => '0'],
            ['text' => '4', 'is_correct' => '1'],
        ],
    ])->assertRedirect();

    // Texte à trous
    $this->actingAs($formateur)->post(route('formateur.questionnaires.questions.store', $questionnaire), [
        'type' => 'cloze',
        'question_text' => 'Complétez.',
        'cloze_raw_text' => 'La capitale de la France est {{capitale}}.',
        'cloze_blanks' => ['capitale' => ['accepted_answers' => 'Paris', 'points' => '1']],
    ])->assertRedirect();

    $questionnaire->refresh();
    expect($questionnaire->questions()->count())->toBe(4);

    // Lancement pour le groupe précis (restriction d'accès réelle)
    $launchResponse = $this->actingAs($formateur)->post(route('formateur.group-quiz.launch'), [
        'group_id' => $group->id,
        'questionnaire_id' => $questionnaire->id,
    ]);
    $session = GroupQuizSession::query()->firstOrFail();
    $launchResponse->assertRedirect(route('formateur.group-quiz.show', $session));
    expect($session->total_questions)->toBe(4);

    $this->actingAs($formateur)->get(route('formateur.group-quiz.show', $session))->assertOk();

    // Un stagiaire hors groupe ne peut pas rejoindre
    $outsider = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);
    $this->actingAs($outsider)
        ->get(route('stagiaire.group-quiz.join-code', ['code' => $session->access_code]))
        ->assertForbidden();

    // Démarrage + réponse correcte à la première question (single)
    $this->actingAs($formateur)->post(route('formateur.group-quiz.start', $session))->assertRedirect();

    $this->actingAs($student)->get(route('stagiaire.group-quiz.show', $session))->assertOk();

    $firstQuestion = $session->sessionQuestions()->orderBy('position')->first()->question;
    $correctOptionId = $firstQuestion->options()->where('is_correct', true)->value('id');

    $this->actingAs($student)
        ->post(route('stagiaire.group-quiz.answer', $session), ['answer' => $correctOptionId])
        ->assertRedirect();

    expect(\App\Models\GroupQuizSessionAnswer::query()->where('is_correct', true)->count())->toBe(1);

    // Cycle jusqu'à la clôture
    $this->actingAs($formateur)->post(route('formateur.group-quiz.reveal', $session))->assertRedirect();
    for ($i = 0; $i < 4; $i++) {
        $this->actingAs($formateur)->post(route('formateur.group-quiz.next', $session))->assertRedirect();
    }

    $session->refresh();
    expect($session->status)->toBe(GroupQuizSession::STATUS_CLOSED);
});
