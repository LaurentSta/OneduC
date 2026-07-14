<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

function creerContexteTriCartesTest(): array
{
    $formateur = User::factory()->create(['role' => 'formateur']);
    $coFormateur = User::factory()->create(['role' => 'formateur']);
    $stagiaire = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);
    $exterieur = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $group = Group::query()->create([
        'name' => 'Groupe tri cartes '.uniqid(),
        'instructor_id' => $formateur->id,
        'is_active' => true,
    ]);

    DB::table('group_user')->insert([
        ['group_id' => $group->id, 'user_id' => $coFormateur->id, 'role_in_group' => 'formateur'],
        ['group_id' => $group->id, 'user_id' => $stagiaire->id, 'role_in_group' => 'stagiaire'],
    ]);

    return compact('formateur', 'coFormateur', 'stagiaire', 'exterieur', 'group');
}

test('le formateur crée une activité, des catégories et des cartes', function () {
    ['formateur' => $formateur, 'group' => $group] = creerContexteTriCartesTest();

    $this->actingAs($formateur)
        ->post(route('formateur.tri-cartes.store'), [
            'group_id' => $group->id,
            'title' => 'Classer les étapes',
        ])
        ->assertRedirect();

    $session = DB::table('card_sort_sessions')->first();
    expect($session)->not->toBeNull()
        ->and($session->group_id)->toBe($group->id);

    $this->actingAs($formateur)
        ->post(route('formateur.tri-cartes.categories.store', $session->id), ['label' => 'Vrai'])
        ->assertRedirect();
    $this->actingAs($formateur)
        ->post(route('formateur.tri-cartes.categories.store', $session->id), ['label' => 'Faux'])
        ->assertRedirect();

    $categories = DB::table('card_sort_categories')->where('card_sort_session_id', $session->id)->orderBy('id')->get();
    expect($categories)->toHaveCount(2);

    $this->actingAs($formateur)
        ->post(route('formateur.tri-cartes.cartes.store', $session->id), [
            'text' => 'Le ciel est bleu',
            'correct_category_id' => $categories[0]->id,
        ])
        ->assertRedirect();

    expect(DB::table('card_sort_cards')->where('card_sort_session_id', $session->id)->count())->toBe(1);
});

test('le stagiaire soumet un classement et obtient un score corrigé côté serveur', function () {
    ['formateur' => $formateur, 'stagiaire' => $stagiaire, 'exterieur' => $exterieur, 'group' => $group] = creerContexteTriCartesTest();

    $sessionId = DB::table('card_sort_sessions')->insertGetId([
        'formateur_id' => $formateur->id,
        'group_id' => $group->id,
        'title' => 'Test',
        'access_code' => 'TC1234',
        'is_active' => true,
        'opened_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $categorieVraiId = DB::table('card_sort_categories')->insertGetId([
        'card_sort_session_id' => $sessionId, 'label' => 'Vrai', 'position' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $categorieFauxId = DB::table('card_sort_categories')->insertGetId([
        'card_sort_session_id' => $sessionId, 'label' => 'Faux', 'position' => 2, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $carte1 = DB::table('card_sort_cards')->insertGetId([
        'card_sort_session_id' => $sessionId, 'correct_category_id' => $categorieVraiId, 'text' => 'A', 'position' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $carte2 = DB::table('card_sort_cards')->insertGetId([
        'card_sort_session_id' => $sessionId, 'correct_category_id' => $categorieFauxId, 'text' => 'B', 'position' => 2, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($stagiaire)
        ->get(route('tri-cartes.join.code', 'TC1234'))
        ->assertOk()
        ->assertSee('Vrai');

    // Le stagiaire place carte1 correctement mais carte2 dans la mauvaise catégorie.
    $reponse = $this->actingAs($stagiaire)
        ->postJson(route('tri-cartes.submit', 'TC1234'), [
            'placements' => [
                $carte1 => $categorieVraiId,
                $carte2 => $categorieVraiId,
            ],
        ])
        ->assertOk();

    $reponse->assertJson(['score' => 1, 'total' => 2]);

    $this->assertDatabaseHas('card_sort_attempts', [
        'card_sort_session_id' => $sessionId,
        'user_id' => $stagiaire->id,
        'score' => 1,
        'total' => 2,
    ]);

    $this->actingAs($exterieur)
        ->postJson(route('tri-cartes.submit', 'TC1234'), ['placements' => [$carte1 => $categorieVraiId]])
        ->assertForbidden();
});

test('les cartes à trier apparaissent dans le hub formateur', function () {
    ['formateur' => $formateur] = creerContexteTriCartesTest();

    $this->actingAs($formateur)
        ->get(route('formateur.outils.index'))
        ->assertOk()
        ->assertSee('Cartes à trier');
});

test('le domaine tri cartes ne dépend pas d eloquent', function () {
    $fichiers = collect(File::allFiles(app_path('Domains/Outils/TriCartes')))
        ->filter(fn (SplFileInfo $fichier): bool => $fichier->getExtension() === 'php');

    foreach ($fichiers as $fichier) {
        $contenu = File::get($fichier->getPathname());

        expect($contenu)
            ->not->toContain('Illuminate\\Database\\Eloquent')
            ->not->toContain('App\\Models');
    }
});
