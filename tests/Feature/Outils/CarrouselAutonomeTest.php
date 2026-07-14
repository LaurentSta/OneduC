<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

function creerContexteCarrouselTest(): array
{
    $formateur = User::factory()->create(['role' => 'formateur']);
    $coFormateur = User::factory()->create(['role' => 'formateur']);
    $stagiaire = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);
    $exterieur = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $group = Group::query()->create([
        'name' => 'Groupe carrousel '.uniqid(),
        'instructor_id' => $formateur->id,
        'is_active' => true,
    ]);

    DB::table('group_user')->insert([
        ['group_id' => $group->id, 'user_id' => $coFormateur->id, 'role_in_group' => 'formateur'],
        ['group_id' => $group->id, 'user_id' => $stagiaire->id, 'role_in_group' => 'stagiaire'],
    ]);

    return compact('formateur', 'coFormateur', 'stagiaire', 'exterieur', 'group');
}

test('le formateur crée un carrousel et y ajoute une slide avec image', function () {
    Storage::fake('public');

    ['formateur' => $formateur, 'coFormateur' => $coFormateur, 'group' => $group] = creerContexteCarrouselTest();

    $this->actingAs($formateur)
        ->post(route('formateur.carrousel.store'), [
            'group_id' => $group->id,
            'title' => 'Les étapes du projet',
        ])
        ->assertRedirect();

    $session = DB::table('carousel_sessions')->first();
    expect($session)->not->toBeNull()
        ->and($session->group_id)->toBe($group->id)
        ->and(strlen($session->access_code))->toBe(6);

    $this->actingAs($formateur)
        ->post(route('formateur.carrousel.slides.store', $session->id), [
            'text' => 'Première étape',
            'image' => UploadedFile::fake()->image('slide.jpg'),
        ])
        ->assertRedirect();

    $slide = DB::table('carousel_slides')->where('carousel_session_id', $session->id)->first();
    expect($slide)->not->toBeNull()
        ->and($slide->text)->toBe('Première étape');
    Storage::disk('public')->assertExists($slide->image_path);

    $this->actingAs($coFormateur)
        ->get(route('formateur.carrousel.show', $session->id))
        ->assertOk()
        ->assertSee('Première étape');
});

test('seul un membre du groupe peut consulter le carrousel via le code', function () {
    ['formateur' => $formateur, 'stagiaire' => $stagiaire, 'exterieur' => $exterieur, 'group' => $group] = creerContexteCarrouselTest();

    $sessionId = DB::table('carousel_sessions')->insertGetId([
        'formateur_id' => $formateur->id,
        'group_id' => $group->id,
        'title' => 'Carrousel test',
        'access_code' => 'CR1234',
        'is_active' => true,
        'opened_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('carousel_slides')->insert([
        'carousel_session_id' => $sessionId,
        'position' => 1,
        'text' => 'Contenu visible',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($stagiaire)
        ->get(route('carrousel.join.code', 'CR1234'))
        ->assertOk()
        ->assertSee('Contenu visible');

    $this->actingAs($exterieur)
        ->get(route('carrousel.join.code', 'CR1234'))
        ->assertForbidden();
});

test('le carrousel apparaît dans le hub formateur', function () {
    ['formateur' => $formateur] = creerContexteCarrouselTest();

    $this->actingAs($formateur)
        ->get(route('formateur.outils.index'))
        ->assertOk()
        ->assertSee('Carrousel');
});

test('le domaine carrousel ne dépend pas d eloquent', function () {
    $fichiers = collect(File::allFiles(app_path('Domains/Outils/Carrousel')))
        ->filter(fn (SplFileInfo $fichier): bool => $fichier->getExtension() === 'php');

    foreach ($fichiers as $fichier) {
        $contenu = File::get($fichier->getPathname());

        expect($contenu)
            ->not->toContain('Illuminate\\Database\\Eloquent')
            ->not->toContain('App\\Models');
    }
});
