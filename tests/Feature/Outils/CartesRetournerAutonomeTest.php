<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

function creerContexteCartesRetournerTest(): array
{
    $formateur = User::factory()->create(['role' => 'formateur']);
    $coFormateur = User::factory()->create(['role' => 'formateur']);
    $stagiaire = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);
    $exterieur = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $group = Group::query()->create([
        'name' => 'Groupe cartes retourner '.uniqid(),
        'instructor_id' => $formateur->id,
        'is_active' => true,
    ]);

    DB::table('group_user')->insert([
        ['group_id' => $group->id, 'user_id' => $coFormateur->id, 'role_in_group' => 'formateur'],
        ['group_id' => $group->id, 'user_id' => $stagiaire->id, 'role_in_group' => 'stagiaire'],
    ]);

    return compact('formateur', 'coFormateur', 'stagiaire', 'exterieur', 'group');
}

test('le formateur crée une activité et y ajoute une carte recto/verso avec images', function () {
    Storage::fake('public');

    ['formateur' => $formateur, 'coFormateur' => $coFormateur, 'group' => $group] = creerContexteCartesRetournerTest();

    $this->actingAs($formateur)
        ->post(route('formateur.cartes-retourner.store'), [
            'group_id' => $group->id,
            'title' => 'Vocabulaire',
        ])
        ->assertRedirect();

    $session = DB::table('flashcard_sessions')->first();
    expect($session)->not->toBeNull()
        ->and($session->group_id)->toBe($group->id)
        ->and(strlen($session->access_code))->toBe(6);

    $this->actingAs($formateur)
        ->post(route('formateur.cartes-retourner.cartes.store', $session->id), [
            'recto_text' => 'HTML',
            'recto_image' => UploadedFile::fake()->image('recto.jpg'),
            'verso_text' => 'Langage de balisage',
            'verso_image' => UploadedFile::fake()->image('verso.jpg'),
        ])
        ->assertRedirect();

    $carte = DB::table('flashcard_cards')->where('flashcard_session_id', $session->id)->first();
    expect($carte)->not->toBeNull()
        ->and($carte->recto_text)->toBe('HTML')
        ->and($carte->verso_text)->toBe('Langage de balisage');
    Storage::disk('public')->assertExists($carte->recto_image_path);
    Storage::disk('public')->assertExists($carte->verso_image_path);

    $this->actingAs($coFormateur)
        ->get(route('formateur.cartes-retourner.show', $session->id))
        ->assertOk()
        ->assertSee('HTML');
});

test('seul un membre du groupe peut consulter les cartes via le code', function () {
    ['formateur' => $formateur, 'stagiaire' => $stagiaire, 'exterieur' => $exterieur, 'group' => $group] = creerContexteCartesRetournerTest();

    $sessionId = DB::table('flashcard_sessions')->insertGetId([
        'formateur_id' => $formateur->id,
        'group_id' => $group->id,
        'title' => 'Test',
        'access_code' => 'FC1234',
        'is_active' => true,
        'opened_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('flashcard_cards')->insert([
        'flashcard_session_id' => $sessionId,
        'position' => 1,
        'recto_text' => 'Question visible',
        'verso_text' => 'Réponse',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($stagiaire)
        ->get(route('cartes-retourner.join.code', 'FC1234'))
        ->assertOk()
        ->assertSee('Question visible');

    $this->actingAs($exterieur)
        ->get(route('cartes-retourner.join.code', 'FC1234'))
        ->assertForbidden();
});

test('les cartes à retourner apparaissent dans le hub formateur', function () {
    ['formateur' => $formateur] = creerContexteCartesRetournerTest();

    $this->actingAs($formateur)
        ->get(route('formateur.outils.index'))
        ->assertOk()
        ->assertSee('Cartes à retourner');
});

test('le domaine cartes à retourner ne dépend pas d eloquent', function () {
    $fichiers = collect(File::allFiles(app_path('Domains/Outils/CartesRetourner')))
        ->filter(fn (SplFileInfo $fichier): bool => $fichier->getExtension() === 'php');

    foreach ($fichiers as $fichier) {
        $contenu = File::get($fichier->getPathname());

        expect($contenu)
            ->not->toContain('Illuminate\\Database\\Eloquent')
            ->not->toContain('App\\Models');
    }
});
