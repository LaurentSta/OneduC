<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

function creerContexteOutilsAutonomes(): array
{
    $formateur = User::factory()->create(['role' => 'formateur']);
    $coFormateur = User::factory()->create(['role' => 'formateur']);
    $stagiaire = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);
    $exterieur = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $group = Group::query()->create([
        'name' => 'Groupe outils autonomes '.uniqid(),
        'instructor_id' => $formateur->id,
        'is_active' => true,
    ]);

    DB::table('group_user')->insert([
        [
            'group_id' => $group->id,
            'user_id' => $coFormateur->id,
            'role_in_group' => 'formateur',
        ],
        [
            'group_id' => $group->id,
            'user_id' => $stagiaire->id,
            'role_in_group' => 'stagiaire',
        ],
    ]);

    return compact('formateur', 'coFormateur', 'stagiaire', 'exterieur', 'group');
}

test('le pendu est créé sans modèle et reste accessible au co-formateur', function () {
    ['formateur' => $formateur, 'coFormateur' => $coFormateur, 'group' => $group] = creerContexteOutilsAutonomes();

    $this->actingAs($formateur)
        ->post(route('formateur.pendu.store'), [
            'group_id' => $group->id,
            'title' => 'Vocabulaire',
            'word' => 'École numérique',
        ])
        ->assertRedirect();

    $session = DB::table('hangman_sessions')->first();

    expect($session)->not->toBeNull()
        ->and($session->group_id)->toBe($group->id)
        ->and(strlen($session->access_code))->toBe(6);

    $this->actingAs($coFormateur)
        ->get(route('formateur.pendu.show', $session->id))
        ->assertOk()
        ->assertSee('École numérique');
});

test('seul un stagiaire du groupe peut proposer une lettre au pendu', function () {
    ['formateur' => $formateur, 'stagiaire' => $stagiaire, 'exterieur' => $exterieur, 'group' => $group] = creerContexteOutilsAutonomes();

    $sessionId = DB::table('hangman_sessions')->insertGetId([
        'formateur_id' => $formateur->id,
        'group_id' => $group->id,
        'title' => 'Pendu',
        'word' => 'Écran',
        'max_attempts' => 6,
        'guessed_letters' => json_encode([]),
        'status' => 'in_progress',
        'access_code' => 'PD1234',
        'is_active' => true,
        'opened_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($stagiaire)
        ->get(route('pendu.join.code', 'PD1234'))
        ->assertOk()
        ->assertSee('Proposer une lettre');

    $this->actingAs($stagiaire)
        ->post(route('pendu.submit', 'PD1234'), ['letter' => 'é'])
        ->assertRedirect();

    $this->assertDatabaseHas('hangman_guesses', [
        'hangman_session_id' => $sessionId,
        'user_id' => $stagiaire->id,
        'letter' => 'E',
        'correct' => true,
    ]);

    $this->actingAs($exterieur)
        ->post(route('pendu.submit', 'PD1234'), ['letter' => 'A'])
        ->assertForbidden();

    expect(DB::table('hangman_guesses')->count())->toBe(1);
});

test('le jeu de mémoire recalcule les erreurs côté serveur', function () {
    ['formateur' => $formateur, 'stagiaire' => $stagiaire, 'exterieur' => $exterieur, 'group' => $group] = creerContexteOutilsAutonomes();

    $this->actingAs($formateur)
        ->post(route('formateur.memoire.store'), [
            'group_id' => $group->id,
            'title' => 'Paires numériques',
            'pairs' => [
                ['a' => 'HTML', 'b' => 'Balisage'],
                ['a' => 'CSS', 'b' => 'Style'],
                ['a' => 'PHP', 'b' => 'Serveur'],
            ],
        ])
        ->assertRedirect();

    $session = DB::table('memory_sessions')->first();

    $this->actingAs($formateur)
        ->get(route('formateur.memoire.show', $session->id))
        ->assertOk()
        ->assertSee('Classement en direct');

    $this->actingAs($stagiaire)
        ->get(route('memoire.join.code', $session->access_code))
        ->assertOk()
        ->assertSee('Commencer');

    $this->actingAs($stagiaire)
        ->postJson(route('memoire.submit', $session->access_code), [
            'moves' => 5,
            'errors' => 99,
            'duration_seconds' => 18,
        ])
        ->assertOk();

    $this->assertDatabaseHas('memory_attempts', [
        'memory_session_id' => $session->id,
        'user_id' => $stagiaire->id,
        'moves' => 5,
        'errors' => 2,
        'duration_seconds' => 18,
    ]);

    $this->actingAs($exterieur)
        ->postJson(route('memoire.submit', $session->access_code), [
            'moves' => 3,
            'duration_seconds' => 10,
        ])
        ->assertForbidden();
});

test('les domaines pendu et mémoire ne dépendent pas d eloquent', function () {
    $fichiers = collect([
        ...File::allFiles(app_path('Domains/Outils/Pendu')),
        ...File::allFiles(app_path('Domains/Outils/Memoire')),
    ])->filter(fn (SplFileInfo $fichier): bool => $fichier->getExtension() === 'php');

    foreach ($fichiers as $fichier) {
        $contenu = File::get($fichier->getPathname());

        expect($contenu)
            ->not->toContain('Illuminate\\Database\\Eloquent')
            ->not->toContain('App\\Models');
    }
});

test('les outils autonomes apparaissent dans le hub formateur', function () {
    ['formateur' => $formateur] = creerContexteOutilsAutonomes();

    $this->actingAs($formateur)
        ->get(route('formateur.outils.index'))
        ->assertOk()
        ->assertSee('Jeu du pendu')
        ->assertSee('Jeu de mémoire');
});
