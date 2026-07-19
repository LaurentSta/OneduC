<?php

use App\Models\Category;
use App\Models\Module;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Support\Str;

it('ne liste dans les catégories publiques que les versions publiées du catalogue même pour un admin', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $formateur = User::factory()->create(['role' => 'formateur']);
    $categorie = Category::query()->create([
        'category_name' => 'Catalogue public '.Str::random(6),
        'category_slug' => 'catalogue-public-'.Str::random(8),
    ]);
    $sousCategorie = SubCategory::query()->create([
        'category_id' => $categorie->id,
        'subcategory_name' => 'Catalogue filtré',
        'subcategory_slug' => 'catalogue-filtre-'.Str::random(8),
    ]);

    $creerModule = function (string $titre, array $attributs) use ($categorie, $sousCategorie, $formateur): Module {
        return Module::query()->create(array_merge([
            'category_id' => $categorie->id,
            'subcategory_id' => $sousCategorie->id,
            'formateur_id' => $formateur->id,
            'module_title' => $titre,
            'module_name' => $titre,
            'module_name_slug' => Str::slug($titre).'-'.Str::random(6),
        ], $attributs));
    };

    $publiee = $creerModule('Formation publique', [
        'is_trainer_authored' => false,
        'publication_state' => Module::PUBLICATION_PUBLISHED,
        'published_at' => now(),
        'status' => true,
    ]);
    $creerModule('Brouillon caché', [
        'is_trainer_authored' => false,
        'publication_state' => Module::PUBLICATION_DRAFT,
        'status' => false,
    ]);
    $creerModule('Archive épinglée cachée', [
        'is_trainer_authored' => false,
        'publication_state' => Module::PUBLICATION_ARCHIVED,
        'status' => true,
    ]);
    $creerModule('Création personnelle cachée', [
        'is_trainer_authored' => true,
        'publication_state' => Module::PUBLICATION_DRAFT,
        'status' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('frontend.category.modules', $categorie))
        ->assertOk()
        ->assertViewHas('modules', fn ($modules) => $modules->pluck('id')->all() === [$publiee->id]);

    $this->actingAs($admin)
        ->get(route('frontend.subcategory.modules', $categorie))
        ->assertOk()
        ->assertViewHas('subcategories', function ($sousCategories) use ($sousCategorie, $publiee): bool {
            $resultat = $sousCategories->firstWhere('id', $sousCategorie->id);

            return $resultat !== null
                && $resultat->modules->pluck('id')->all() === [$publiee->id];
        });
});
