<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function createParcoursFormateurUser(): User
{
    return User::query()->create([
        'prenom' => 'Laurence',
        'name' => 'Formatrice',
        'username' => 'laurence.formatrice',
        'email' => 'laurence.formatrice@example.test',
        'password' => Hash::make('password'),
        'role' => 'formateur',
        'status' => true,
    ]);
}

function parcoursActivityRoute(string $routeName): string
{
    return route($routeName, [
        'module' => 'organiser-ses-parcours',
        'chapter' => 'preparer-les-contenus',
        'lesson' => 'retrouver-les-espaces-de-preparation',
        'activity' => 'classer-les-elements',
    ]);
}

it('shows the linked activity directly after the lesson in the parcours sidebar', function () {
    $formateur = createParcoursFormateurUser();

    $response = $this
        ->actingAs($formateur)
        ->get(route('formateur.parcours.lessons.show', [
            'module' => 'organiser-ses-parcours',
            'chapter' => 'preparer-les-contenus',
            'lesson' => 'retrouver-les-espaces-de-preparation',
        ]));

    $response->assertOk();
    $response->assertSee('Activité');
    $response->assertDontSee('Activité - Classer les éléments de préparation');
    $response->assertDontSee('1 page');
    $response->assertSee(route('formateur.parcours.activities.show', [
        'module' => 'organiser-ses-parcours',
        'chapter' => 'preparer-les-contenus',
        'lesson' => 'retrouver-les-espaces-de-preparation',
        'activity' => 'classer-les-elements',
    ]), false);
});

it('stores a failed parcours activity attempt when elements are missing or misplaced', function () {
    $formateur = createParcoursFormateurUser();
    $token = 'csrf-parcours-activity-failed';

    $response = $this
        ->withSession(['_token' => $token])
        ->withHeader('X-CSRF-TOKEN', $token)
        ->actingAs($formateur)
        ->postJson(parcoursActivityRoute('formateur.parcours.activities.submit'), [
            '_token' => $token,
            'placements' => [
                'information' => ['pierre_dupont', 'date_debut'],
                'stagiaire' => ['titre_groupe'],
                'module' => [],
            ],
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => false,
    ]);
    expect($response->json('wrong_item_ids'))->toContain('pierre_dupont');

    $attempt = DB::table('trainer_path_activity_attempts')->latest('id')->first();

    expect($attempt)->not->toBeNull();
    expect((bool) $attempt->is_success)->toBeFalse();
    expect((int) $attempt->correct_items)->toBeGreaterThanOrEqual(1);
    expect((string) $attempt->activity_key)->toBe('classer-les-elements');
});

it('stores a successful parcours activity attempt and reopens the activity as validated', function () {
    $formateur = createParcoursFormateurUser();
    $token = 'csrf-parcours-activity-success';

    $response = $this
        ->withSession(['_token' => $token])
        ->withHeader('X-CSRF-TOKEN', $token)
        ->actingAs($formateur)
        ->postJson(parcoursActivityRoute('formateur.parcours.activities.submit'), [
            '_token' => $token,
            'placements' => [
                'information' => ['titre_groupe', 'date_debut', 'date_fin'],
                'stagiaire' => ['pierre_dupont', 'aurelie_martin', 'email_stagiaire'],
                'module' => ['module_word_debutant', 'module_excel_avance', 'module_powerpoint'],
            ],
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
    ]);

    $attempt = DB::table('trainer_path_activity_attempts')->latest('id')->first();

    expect($attempt)->not->toBeNull();
    expect((bool) $attempt->is_success)->toBeTrue();
    expect((int) $attempt->correct_items)->toBe(9);

    $page = $this
        ->actingAs($formateur)
        ->get(parcoursActivityRoute('formateur.parcours.activities.show'));

    $page->assertOk();
    $page->assertSee('Activité validée');
    $page->assertSee('Leçon suivante');
});
