<?php

use App\Models\Category;
use App\Models\Module;
use App\Models\SubCategory;
use App\Models\User;

function makeCatalogTestModule(array $overrides = []): Module
{
    $category = Category::query()->create([
        'category_name' => 'Categorie publique ' . uniqid(),
        'category_slug' => 'categorie-publique-' . uniqid(),
    ]);
    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie publique',
        'subcategory_slug' => 'sous-categorie-publique-' . uniqid(),
    ]);
    $formateur = User::factory()->create(['role' => 'formateur']);

    return Module::query()->create(array_merge([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module ' . uniqid(),
        'module_name' => 'Module ' . uniqid(),
        'module_name_slug' => 'module-' . uniqid(),
        'status' => 1,
        'is_trainer_authored' => false,
    ], $overrides));
}

it('excludes trainer-authored modules from the public catalog listing', function () {
    $public = makeCatalogTestModule();
    $trainerAuthored = makeCatalogTestModule(['is_trainer_authored' => true]);

    $response = $this->get(route('frontend.modules.index'));

    $response->assertOk();
    $response->assertSee($public->module_title);
    $response->assertDontSee($trainerAuthored->module_title);
});

it('excludes inactive modules from the public catalog listing', function () {
    $active = makeCatalogTestModule();
    $inactive = makeCatalogTestModule(['status' => 0]);

    $response = $this->get(route('frontend.modules.index'));

    $response->assertOk();
    $response->assertSee($active->module_title);
    $response->assertDontSee($inactive->module_title);
});

it('returns 404 when trying to open a trainer-authored module detail page anonymously', function () {
    $trainerAuthored = makeCatalogTestModule(['is_trainer_authored' => true]);

    $this->get(route('frontend.modules.show', ['category' => $trainerAuthored->category_id, 'module' => $trainerAuthored->id]))
        ->assertNotFound();
});

it('returns 404 when trying to open an inactive module detail page anonymously', function () {
    $inactive = makeCatalogTestModule(['status' => 0]);

    $this->get(route('frontend.modules.show', ['category' => $inactive->category_id, 'module' => $inactive->id]))
        ->assertNotFound();
});
