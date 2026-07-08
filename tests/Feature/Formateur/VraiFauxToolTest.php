<?php

use App\Models\Group;
use App\Models\TrueFalseSession;
use App\Models\TrueFalseSessionResponse;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function createVraiFauxContext(): array
{
    $formateur = User::factory()->create(['role' => 'formateur']);
    $stagiaire = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $group = Group::query()->create([
        'name' => 'Groupe Vrai Faux '.uniqid(),
        'instructor_id' => $formateur->id,
        'is_active' => true,
    ]);

    DB::table('group_user')->insert([
        'group_id' => $group->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
    ]);

    return compact('formateur', 'stagiaire', 'group');
}

test('a formateur can create a true false session for an accessible group', function () {
    ['formateur' => $formateur, 'group' => $group] = createVraiFauxContext();

    $this->actingAs($formateur)
        ->post(route('formateur.vraifaux.store'), [
            'group_id' => $group->id,
            'title' => 'Mythes numériques',
            'questions' => [
                [
                    'statement' => 'Un mot de passe long est plus résistant.',
                    'answer' => '1',
                    'explanation' => 'La longueur augmente fortement le nombre de combinaisons.',
                ],
                [
                    'statement' => 'Il faut partager son mot de passe avec son formateur.',
                    'answer' => '0',
                ],
            ],
        ])
        ->assertRedirect();

    $session = TrueFalseSession::query()->first();

    expect($session)->not->toBeNull()
        ->and($session->group_id)->toBe($group->id)
        ->and($session->title)->toBe('Mythes numériques')
        ->and($session->questions)->toHaveCount(2)
        ->and(strlen($session->access_code))->toBe(6)
        ->and($session->is_active)->toBeTrue();
});

test('only a stagiaire from the group can answer a true false session', function () {
    ['formateur' => $formateur, 'stagiaire' => $stagiaire, 'group' => $group] = createVraiFauxContext();
    $outsider = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $session = TrueFalseSession::query()->create([
        'formateur_id' => $formateur->id,
        'group_id' => $group->id,
        'title' => 'Vrai ou Faux',
        'questions' => [
            [
                'statement' => 'Oneduc vérifie les accès par groupe.',
                'answer' => true,
                'explanation' => null,
            ],
        ],
        'access_code' => 'VF1234',
        'is_active' => true,
        'opened_at' => now(),
    ]);

    $this->actingAs($stagiaire)
        ->post(route('vraifaux.submit', $session->access_code), [
            'question_index' => 0,
            'answer' => '1',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('true_false_session_responses', [
        'true_false_session_id' => $session->id,
        'user_id' => $stagiaire->id,
        'question_index' => 0,
        'answer' => 1,
    ]);

    $this->actingAs($outsider)
        ->post(route('vraifaux.submit', $session->access_code), [
            'question_index' => 0,
            'answer' => '0',
        ])
        ->assertForbidden();

    expect(TrueFalseSessionResponse::query()->count())->toBe(1);
});
