<?php

use App\Models\Group;
use App\Models\GroupWhiteboard;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function createWhiteboardUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role) . ' Whiteboard',
        'username' => $role . '_whiteboard_' . uniqid(),
        'email' => $role . '.whiteboard.' . uniqid() . '@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function createWhiteboardGroup(User $formateur, array $students = []): Group
{
    $group = Group::query()->create([
        'name' => 'Groupe blanc ' . uniqid(),
        'description' => 'Groupe de test pour le tableau blanc',
        'temporary_password' => 'temp-password',
        'instructor_id' => $formateur->id,
    ]);

    foreach ($students as $student) {
        DB::table('group_user')->insert([
            'group_id' => $group->id,
            'user_id' => $student->id,
            'role_in_group' => 'stagiaire',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return $group;
}

it('allows the instructor to open the collaborative whiteboard of a group', function () {
    $formateur = createWhiteboardUser('formateur');
    $group = createWhiteboardGroup($formateur);

    $response = $this->actingAs($formateur)
        ->get(route('formateur.groupes.whiteboard.show', ['group' => $group->id]));

    $response->assertOk();
    $response->assertSee('Tableau blanc collaboratif');
    $this->assertDatabaseHas('group_whiteboards', ['group_id' => $group->id]);
});

it('allows a student of the group to create an item and expose it in the snapshot', function () {
    $formateur = createWhiteboardUser('formateur');
    $stagiaire = createWhiteboardUser('stagiaire');
    $otherStagiaire = createWhiteboardUser('stagiaire');
    $group = createWhiteboardGroup($formateur, [$stagiaire, $otherStagiaire]);
    $token = 'csrf-whiteboard';

    $saveResponse = $this->actingAs($stagiaire)
        ->withSession(['_token' => $token])
        ->postJson(route('stagiaire.whiteboard.items.upsert', ['group' => $group->id]), [
            '_token' => $token,
            'client_uuid' => 'note-test-1',
            'type' => 'note',
            'x' => 120,
            'y' => 90,
            'width' => 260,
            'height' => 180,
            'rotation' => 0,
            'z_index' => 1,
            'payload' => ['content' => 'Bonjour le groupe'],
            'style' => ['fill' => '#FDE68A', 'textColor' => '#172033'],
        ]);

    $saveResponse->assertOk()
        ->assertJsonPath('item.type', 'note')
        ->assertJsonPath('item.payload.content', 'Bonjour le groupe');

    $whiteboard = GroupWhiteboard::query()->where('group_id', $group->id)->first();

    expect($whiteboard)->not->toBeNull();
    expect((int) $whiteboard->version)->toBe(1);

    $snapshotResponse = $this->actingAs($otherStagiaire)
        ->getJson(route('stagiaire.whiteboard.snapshot', ['group' => $group->id]));

    $snapshotResponse->assertOk()
        ->assertJsonPath('version', 1)
        ->assertJsonPath('items.0.type', 'note')
        ->assertJsonPath('items.0.payload.content', 'Bonjour le groupe');
});

it('denies access to a student outside the group', function () {
    $formateur = createWhiteboardUser('formateur');
    $stagiaire = createWhiteboardUser('stagiaire');
    $intrus = createWhiteboardUser('stagiaire');
    $group = createWhiteboardGroup($formateur, [$stagiaire]);

    $response = $this->actingAs($intrus)
        ->get(route('stagiaire.whiteboard.show', ['group' => $group->id]));

    $response->assertNotFound();
});
