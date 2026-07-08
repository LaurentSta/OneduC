<?php

use App\Models\ComponentFinderAttempt;
use App\Models\ComponentFinderSession;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

function createComponentFinderToolContext(): array
{
    $formateur = User::factory()->create(['role' => 'formateur']);
    $stagiaire = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $group = Group::query()->create([
        'name' => 'Groupe Composants '.uniqid(),
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

test('a formateur can create a component finder session for an accessible group', function () {
    Storage::fake('public');
    ['formateur' => $formateur, 'group' => $group] = createComponentFinderToolContext();

    $zones = [
        ['label' => 'Clavier', 'shape' => 'rectangle', 'x' => 10, 'y' => 10, 'w' => 20, 'h' => 10],
        ['label' => 'Écran', 'shape' => 'rectangle', 'x' => 45, 'y' => 15, 'w' => 30, 'h' => 20],
    ];

    $this->actingAs($formateur)
        ->post(route('formateur.composants.store'), [
            'group_id' => $group->id,
            'title' => 'Poste informatique',
            'image' => UploadedFile::fake()->image('ordinateur.png', 640, 360),
            'zones' => json_encode($zones),
        ])
        ->assertRedirect();

    $session = ComponentFinderSession::query()->first();

    expect($session)->not->toBeNull()
        ->and($session->group_id)->toBe($group->id)
        ->and($session->title)->toBe('Poste informatique')
        ->and($session->zones)->toHaveCount(2)
        ->and(strlen($session->access_code))->toBe(6)
        ->and($session->is_active)->toBeTrue();

    Storage::disk('public')->assertExists($session->image_path);
});

test('only a stagiaire from the group can submit a component finder attempt', function () {
    ['formateur' => $formateur, 'stagiaire' => $stagiaire, 'group' => $group] = createComponentFinderToolContext();
    $outsider = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $session = ComponentFinderSession::query()->create([
        'formateur_id' => $formateur->id,
        'group_id' => $group->id,
        'title' => 'Trouve le composant',
        'image_path' => 'component_finder/demo.png',
        'zones' => [
            ['label' => 'Clavier', 'shape' => 'rectangle', 'x' => 10, 'y' => 10, 'w' => 20, 'h' => 10],
            ['label' => 'Écran', 'shape' => 'rectangle', 'x' => 45, 'y' => 15, 'w' => 30, 'h' => 20],
        ],
        'access_code' => 'CF1234',
        'is_active' => true,
        'opened_at' => now(),
    ]);

    $payload = [
        'score' => 2,
        'total' => 2,
        'duration_seconds' => 12,
        'details' => [
            ['label' => 'Clavier', 'correct' => true, 'time_ms' => 4000],
            ['label' => 'Écran', 'correct' => true, 'time_ms' => 8000],
        ],
    ];

    $this->actingAs($stagiaire)
        ->postJson(route('composants.submit', $session->access_code), $payload)
        ->assertOk();

    $this->assertDatabaseHas('component_finder_attempts', [
        'component_finder_session_id' => $session->id,
        'user_id' => $stagiaire->id,
        'score' => 2,
        'total' => 2,
    ]);

    $this->actingAs($outsider)
        ->postJson(route('composants.submit', $session->access_code), $payload)
        ->assertForbidden();

    expect(ComponentFinderAttempt::query()->count())->toBe(1);
});
