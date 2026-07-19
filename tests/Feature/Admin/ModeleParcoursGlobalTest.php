<?php

use App\Models\Category;
use App\Models\FormateurParcours;
use App\Models\ModeleParcours;
use App\Models\Module;
use App\Models\SubCategory;
use App\Models\User;
use App\Support\Parcours\RegistreOutilsParcours;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    if (! Route::has('admin.modeles-parcours.index')) {
        require base_path('routes/admin-modeles-parcours.php');
    }
});

function creerModuleOfficielPourModele(User $admin, bool $actif = true): Module
{
    $categorie = Category::query()->create([
        'category_name' => 'Catégorie modèle '.uniqid(),
        'category_slug' => 'categorie-modele-'.uniqid(),
    ]);

    $sousCategorie = SubCategory::query()->create([
        'category_id' => $categorie->id,
        'subcategory_name' => 'Sous-catégorie modèle',
        'subcategory_slug' => 'sous-categorie-modele-'.uniqid(),
    ]);

    return Module::query()->create([
        'category_id' => $categorie->id,
        'subcategory_id' => $sousCategorie->id,
        'formateur_id' => $admin->id,
        'is_trainer_authored' => false,
        'module_title' => 'Formation officielle '.uniqid(),
        'module_name' => 'Formation officielle',
        'module_name_slug' => 'formation-officielle-'.uniqid(),
        'status' => $actif,
        'publication_state' => $actif ? Module::PUBLICATION_PUBLISHED : Module::PUBLICATION_DRAFT,
        'published_at' => $actif ? now() : null,
    ]);
}

it('permet à un administrateur de créer puis publier explicitement un modèle global', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $module = creerModuleOfficielPourModele($admin);

    $reponseCreation = $this->actingAs($admin)->post(route('admin.modeles-parcours.store'), [
        'titre' => 'Parcours inclusion numérique',
        'description' => 'Modèle officiel Oneduc.',
        'items' => [
            ['type' => 'module', 'module_id' => $module->id],
            [
                'type' => 'outil',
                'outil' => 'minuteur',
                'configuration' => json_encode([
                    'titre' => 'Pause active',
                    'consigne' => 'Réaliser l’exercice en groupe.',
                    'duree_secondes' => 300,
                ]),
            ],
        ],
    ]);

    $modele = ModeleParcours::query()->where('titre', 'Parcours inclusion numérique')->firstOrFail();

    $reponseCreation->assertRedirect(route('admin.modeles-parcours.edit', $modele));
    expect($modele->statut)->toBe(ModeleParcours::STATUT_BROUILLON)
        ->and($modele->auteur_admin_id)->toBe($admin->id)
        ->and($modele->items()->count())->toBe(2);

    $reponsePublication = $this->actingAs($admin)
        ->post(route('admin.modeles-parcours.publier', $modele));

    $reponsePublication->assertRedirect(route('admin.modeles-parcours.index'));
    expect($modele->fresh()->statut)->toBe(ModeleParcours::STATUT_PUBLIE)
        ->and($modele->fresh()->publie_le)->not->toBeNull();

    $this->actingAs($admin)
        ->put(route('admin.modeles-parcours.update', $modele), [
            'titre' => 'Modification interdite',
            'items' => [['type' => 'module', 'module_id' => $module->id]],
        ])
        ->assertStatus(409);
});

it('isole les routes administrateur des comptes formateur', function () {
    $formateur = User::factory()->create(['role' => 'formateur']);

    $this->actingAs($formateur)
        ->get(route('admin.modeles-parcours.index'))
        ->assertRedirect('/connexion');
});

it('duplique un modèle publié au nom du formateur sans créer de données runtime', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $formateur = User::factory()->create(['role' => 'formateur']);
    $module = creerModuleOfficielPourModele($admin);

    $modele = ModeleParcours::query()->create([
        'auteur_admin_id' => $admin->id,
        'titre' => 'Parcours duplicable',
        'description' => 'Structure sans sessions.',
        'statut' => ModeleParcours::STATUT_PUBLIE,
        'publie_le' => now(),
    ]);
    $modele->items()->create([
        'position' => 1,
        'type' => 'module',
        'module_id' => $module->id,
    ]);
    $modele->items()->create([
        'position' => 2,
        'type' => 'outil',
        'outil' => 'sondage',
        'configuration' => [
            'titre' => 'Sondage final',
            'consigne' => null,
            'questions' => [[
                'intitule' => 'La formation vous a-t-elle aidé ?',
                'choix' => ['Oui', 'Non'],
            ]],
        ],
    ]);

    $reponse = $this->actingAs($formateur)
        ->post(route('formateur.modeles-parcours.dupliquer', $modele));

    $reponse->assertRedirect(route('formateur.mes-parcours.index'));

    $copie = FormateurParcours::query()->where('formateur_id', $formateur->id)->firstOrFail();
    expect($copie->modele_parcours_id)->toBe($modele->id)
        ->and($copie->title)->toBe($modele->titre)
        ->and($copie->items()->count())->toBe(2);

    $outilCopie = DB::table('formateur_parcours_items')
        ->where('formateur_parcours_id', $copie->id)
        ->where('type', 'outil')
        ->first();
    $configuration = json_decode($outilCopie->configuration, true, 512, JSON_THROW_ON_ERROR);

    expect($outilCopie->outil)->toBe('sondage')
        ->and($configuration)->toHaveKey('questions')
        ->and($configuration)->not->toHaveKeys([
            'access_code',
            'code_acces',
            'responses',
            'reponses',
            'results',
            'resultats',
        ])
        ->and(DB::table('poll_sessions')->count())->toBe(0)
        ->and(DB::table('word_clouds')->count())->toBe(0)
        ->and(DB::table('live_quiz_sessions')->count())->toBe(0);
});

it('refuse toute donnée de session dans une configuration de modèle', function () {
    $registre = app(RegistreOutilsParcours::class);

    expect(fn () => $registre->valider('minuteur', [
        'titre' => 'Minuteur compromis',
        'consigne' => null,
        'duree_secondes' => 120,
        'access_code' => 'ABC123',
    ]))->toThrow(ValidationException::class);
});

it('refuse de publier un modèle qui référence une formation inactive', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $module = creerModuleOfficielPourModele($admin, false);
    $modele = ModeleParcours::query()->create([
        'auteur_admin_id' => $admin->id,
        'titre' => 'Brouillon avec formation inactive',
        'statut' => ModeleParcours::STATUT_BROUILLON,
    ]);
    $modele->items()->create([
        'position' => 1,
        'type' => 'module',
        'module_id' => $module->id,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.modeles-parcours.index'))
        ->post(route('admin.modeles-parcours.publier', $modele))
        ->assertSessionHasErrors('modele');

    expect($modele->fresh()->statut)->toBe(ModeleParcours::STATUT_BROUILLON);
});
