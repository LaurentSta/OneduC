<?php

use App\Models\Category;
use App\Models\Module;
use App\Models\SubCategory;
use App\Models\User;

it('affiche la catégorie sous le titre des formations du catalogue', function () {
    $formateur = User::factory()->create(['role' => 'formateur']);
    $categorie = Category::query()->create([
        'category_name' => 'Compétences numériques',
        'category_slug' => 'competences-numeriques',
    ]);
    $sousCategorie = SubCategory::query()->create([
        'category_id' => $categorie->id,
        'subcategory_name' => 'Premiers usages',
        'subcategory_slug' => 'premiers-usages',
    ]);

    Module::query()->create([
        'category_id' => $categorie->id,
        'subcategory_id' => $sousCategorie->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Découvrir le clavier',
        'module_name' => 'Découvrir le clavier',
        'module_name_slug' => 'decouvrir-le-clavier',
        'status' => 1,
    ]);

    $this->actingAs($formateur)
        ->get(route('formateur.formations.index'))
        ->assertOk()
        ->assertSee('Découvrir le clavier')
        ->assertSee('Catégorie : Compétences numériques');
});

it('filtre le catalogue formateur par catégorie', function () {
    $formateur = User::factory()->create(['role' => 'formateur']);
    $categorieIncluse = Category::query()->create([
        'category_name' => 'Bureautique',
        'category_slug' => 'bureautique',
    ]);
    $categorieExclue = Category::query()->create([
        'category_name' => 'Sécurité numérique',
        'category_slug' => 'securite-numerique',
    ]);
    $sousCategorieIncluse = SubCategory::query()->create([
        'category_id' => $categorieIncluse->id,
        'subcategory_name' => 'Traitement de texte',
        'subcategory_slug' => 'traitement-de-texte',
    ]);
    $sousCategorieExclue = SubCategory::query()->create([
        'category_id' => $categorieExclue->id,
        'subcategory_name' => 'Hameçonnage',
        'subcategory_slug' => 'hameconnage',
    ]);

    Module::query()->create([
        'category_id' => $categorieIncluse->id,
        'subcategory_id' => $sousCategorieIncluse->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Prendre en main un traitement de texte',
        'module_name' => 'Traitement de texte',
        'module_name_slug' => 'traitement-de-texte',
        'status' => 1,
    ]);
    Module::query()->create([
        'category_id' => $categorieExclue->id,
        'subcategory_id' => $sousCategorieExclue->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Reconnaître un message frauduleux',
        'module_name' => 'Message frauduleux',
        'module_name_slug' => 'message-frauduleux',
        'status' => 1,
    ]);

    $this->actingAs($formateur)
        ->get(route('formateur.formations.index', ['categorie' => $categorieIncluse->id]))
        ->assertOk()
        ->assertSee('Prendre en main un traitement de texte')
        ->assertDontSee('Reconnaître un message frauduleux');
});

it('emploie formation dans la catégorie automatique et le message de création', function () {
    $formateur = User::factory()->create(['role' => 'formateur']);
    $ancienneCategorie = Category::query()->create([
        'category_name' => 'Modules formateurs',
        'category_slug' => 'modules-formateurs',
    ]);

    $response = $this->actingAs($formateur)
        ->post(route('formateur.modules.builder.store'), [
            'module_title' => 'Formation vocabulaire',
            'description' => null,
        ]);

    $formation = Module::query()->where('module_title', 'Formation vocabulaire')->firstOrFail();

    $response
        ->assertRedirect(route('formateur.modules.builder.edit', $formation))
        ->assertSessionHas('success', 'Formation créée. Ajoutez maintenant des chapitres et des leçons.');

    expect($ancienneCategorie->fresh()->category_name)->toBe('Formations formateurs');

    $this->actingAs($formateur)
        ->get(route('formateur.formations.index'))
        ->assertOk()
        ->assertSee('Catégorie : Formations formateurs')
        ->assertDontSee('Modules formateurs');
});
