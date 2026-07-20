<?php

use App\Models\FormateurParcours;
use App\Models\FormateurParcoursItem;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('mes-parcours show page renders the linked groups section without error', function () {
    $formateur = User::factory()->create(['role' => 'formateur']);

    $parcours = FormateurParcours::query()->create([
        'formateur_id' => $formateur->id,
        'title' => 'Parcours smoke test',
    ]);

    $item = FormateurParcoursItem::query()->create([
        'formateur_parcours_id' => $parcours->id,
        'position' => 1,
        'type' => 'outil',
        'outil' => 'vrai-faux',
        'configuration' => [
            'titre' => 'Mythes numériques',
            'consigne' => null,
            'affirmations' => [['texte' => 'Test', 'reponse' => true]],
        ],
    ]);

    $group = Group::query()->create([
        'name' => 'Groupe smoke test',
        'instructor_id' => $formateur->id,
        'is_active' => true,
        'formateur_parcours_id' => $parcours->id,
    ]);

    $response = $this->actingAs($formateur)->get(route('formateur.mes-parcours.show', $parcours));

    $response->assertOk();
    $response->assertSeeText('Groupes utilisant ce parcours');
    $response->assertSeeText($group->name);
    $response->assertSee(route('formateur.groupes.outils.launch', [$group, $item]), false);
});

test('mes-parcours show page renders fine with no linked groups', function () {
    $formateur = User::factory()->create(['role' => 'formateur']);

    $parcours = FormateurParcours::query()->create([
        'formateur_id' => $formateur->id,
        'title' => 'Parcours sans groupe',
    ]);

    $response = $this->actingAs($formateur)->get(route('formateur.mes-parcours.show', $parcours));

    $response->assertOk();
    $response->assertSeeText("Aucun groupe n'est encore lié");
});
