<?php

use App\Models\BuzzerAttempt;
use App\Models\BuzzerSession;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function createBuzzerToolContext(): array
{
    $formateur = User::factory()->create(['role' => 'formateur']);
    $stagiaire = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $group = Group::query()->create([
        'name' => 'Groupe Buzzer '.uniqid(),
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

test('a formateur can create a buzzer quiz for an accessible group', function () {
    ['formateur' => $formateur, 'group' => $group] = createBuzzerToolContext();

    $this->actingAs($formateur)
        ->post(route('formateur.buzzer.store'), [
            'group_id' => $group->id,
            'title' => 'Révisions matériel',
            'mode' => 'distance',
            'questions' => [
                'Quel périphérique permet de saisir du texte ?',
                'Quel composant stocke les fichiers ?',
            ],
        ])
        ->assertRedirect();

    $session = BuzzerSession::query()->with('questions')->first();

    expect($session)->not->toBeNull()
        ->and($session->group_id)->toBe($group->id)
        ->and($session->title)->toBe('Révisions matériel')
        ->and($session->mode)->toBe(BuzzerSession::MODE_DISTANCE)
        ->and($session->questions)->toHaveCount(2)
        ->and(strlen($session->access_code))->toBe(6);
});

test('only a stagiaire from the group can buzz', function () {
    ['formateur' => $formateur, 'stagiaire' => $stagiaire, 'group' => $group] = createBuzzerToolContext();
    $outsider = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $session = BuzzerSession::query()->create([
        'formateur_id' => $formateur->id,
        'group_id' => $group->id,
        'title' => 'Buzzer Quiz',
        'mode' => BuzzerSession::MODE_DISTANCE,
        'access_code' => 'BZ1234',
        'status' => BuzzerSession::STATUS_QUESTION_OPEN,
        'current_position' => 1,
        'total_questions' => 1,
        'opened_at' => now(),
    ]);

    $session->questions()->create([
        'position' => 1,
        'question_text' => 'Quel outil vérifie les accès par groupe ?',
        'opened_at' => now(),
    ]);

    $this->actingAs($stagiaire)
        ->postJson(route('buzzer.buzz', $session->access_code))
        ->assertOk();

    $this->assertDatabaseHas('buzzer_attempts', [
        'buzzer_session_id' => $session->id,
        'user_id' => $stagiaire->id,
    ]);

    $this->actingAs($outsider)
        ->postJson(route('buzzer.buzz', $session->access_code))
        ->assertForbidden();

    expect(BuzzerAttempt::query()->count())->toBe(1);
});
