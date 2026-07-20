<?php

use App\Models\FormateurParcours;
use App\Models\FormateurParcoursItem;
use App\Models\Group;
use App\Models\TrueFalseSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function createParcoursVraiFauxContext(array $groupAttributes = []): array
{
    $formateur = User::factory()->create(['role' => 'formateur']);
    $stagiaire = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $parcours = FormateurParcours::query()->create([
        'formateur_id' => $formateur->id,
        'title' => 'Parcours vrai/faux '.uniqid(),
    ]);

    $item = FormateurParcoursItem::query()->create([
        'formateur_parcours_id' => $parcours->id,
        'position' => 1,
        'type' => 'outil',
        'outil' => 'vrai-faux',
        'configuration' => [
            'titre' => 'Mythes numériques',
            'consigne' => null,
            'affirmations' => [
                ['texte' => 'Un mot de passe long est plus résistant.', 'reponse' => true],
                ['texte' => 'Il faut partager son mot de passe avec son formateur.', 'reponse' => false],
            ],
        ],
    ]);

    $group = Group::query()->create(array_merge([
        'name' => 'Groupe parcours vrai/faux '.uniqid(),
        'instructor_id' => $formateur->id,
        'is_active' => true,
        'formateur_parcours_id' => $parcours->id,
    ], $groupAttributes));

    DB::table('group_user')->insert([
        'group_id' => $group->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
    ]);

    return compact('formateur', 'stagiaire', 'parcours', 'item', 'group');
}

test('a formateur can launch a parcours vrai/faux step for a linked group', function () {
    ['formateur' => $formateur, 'item' => $item, 'group' => $group] = createParcoursVraiFauxContext();

    $response = $this->actingAs($formateur)
        ->get(route('formateur.groupes.outils.launch', [$group, $item]));

    $session = TrueFalseSession::query()->first();

    expect($session)->not->toBeNull()
        ->and($session->group_id)->toBe($group->id)
        ->and($session->formateur_parcours_item_id)->toBe($item->id)
        ->and($session->title)->toBe('Mythes numériques')
        ->and($session->questions)->toHaveCount(2)
        ->and($session->is_active)->toBeTrue();

    $response->assertRedirect(route('formateur.vraifaux.show', $session));
});

test('launching the same parcours step twice for the same group is idempotent', function () {
    ['formateur' => $formateur, 'item' => $item, 'group' => $group] = createParcoursVraiFauxContext();

    $this->actingAs($formateur)->get(route('formateur.groupes.outils.launch', [$group, $item]));
    $this->actingAs($formateur)->get(route('formateur.groupes.outils.launch', [$group, $item]));

    expect(TrueFalseSession::query()->count())->toBe(1);
});

test('a co-formateur of the group can also launch a parcours step', function () {
    ['item' => $item, 'group' => $group] = createParcoursVraiFauxContext();

    $coFormateur = User::factory()->create(['role' => 'formateur']);

    DB::table('group_user')->insert([
        'group_id' => $group->id,
        'user_id' => $coFormateur->id,
        'role_in_group' => 'formateur',
    ]);

    $this->actingAs($coFormateur)
        ->get(route('formateur.groupes.outils.launch', [$group, $item]))
        ->assertRedirect();

    expect(TrueFalseSession::query()->count())->toBe(1);
});

test('a formateur unrelated to the group cannot launch a parcours step', function () {
    ['item' => $item, 'group' => $group] = createParcoursVraiFauxContext();

    $outsider = User::factory()->create(['role' => 'formateur']);

    $this->actingAs($outsider)
        ->get(route('formateur.groupes.outils.launch', [$group, $item]))
        ->assertForbidden();

    expect(TrueFalseSession::query()->count())->toBe(0);
});

test('a stagiaire sees the waiting view before the formateur launches the step', function () {
    ['stagiaire' => $stagiaire, 'item' => $item] = createParcoursVraiFauxContext();

    $response = $this->actingAs($stagiaire)
        ->get(route('stagiaire.outil.parcours.show', $item));

    $response->assertOk();
    $response->assertSeeText("n'a pas encore été lancée");
});

test('a stagiaire is redirected to the live tool once the formateur has launched the step', function () {
    ['formateur' => $formateur, 'stagiaire' => $stagiaire, 'item' => $item, 'group' => $group] = createParcoursVraiFauxContext();

    $this->actingAs($formateur)->get(route('formateur.groupes.outils.launch', [$group, $item]));
    $session = TrueFalseSession::query()->firstOrFail();

    $this->actingAs($stagiaire)
        ->get(route('stagiaire.outil.parcours.show', $item))
        ->assertRedirect(route('vraifaux.join.code', $session->access_code));

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
});

test('a stagiaire outside the linked group cannot access the parcours step', function () {
    ['item' => $item] = createParcoursVraiFauxContext();

    $outsider = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $this->actingAs($outsider)
        ->get(route('stagiaire.outil.parcours.show', $item))
        ->assertForbidden();
});
