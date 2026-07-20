<?php

use App\Models\Category;
use App\Models\Module;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

function makeSitemapTestModule(array $overrides = []): Module
{
    $category = Category::query()->create([
        'category_name' => 'Categorie sitemap ' . uniqid(),
        'category_slug' => 'categorie-sitemap-' . uniqid(),
    ]);
    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie sitemap',
        'subcategory_slug' => 'sous-categorie-sitemap-' . uniqid(),
    ]);
    $formateur = User::factory()->create(['role' => 'formateur']);

    return Module::query()->create(array_merge([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module sitemap ' . uniqid(),
        'module_name' => 'Module sitemap ' . uniqid(),
        'module_name_slug' => 'module-sitemap-' . uniqid(),
        'status' => 1,
        'is_trainer_authored' => false,
    ], $overrides));
}

beforeEach(function () {
    Cache::forget('sitemap.xml');
});

it('serves a well formed sitemap with the static pages and an xml content type', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $body = $response->getContent();
    expect(simplexml_load_string($body))->not->toBeFalse();
    expect($body)->toContain(route('index'))
        ->toContain(route('accessibilite'))
        ->toContain(route('accessibilite.schema'))
        ->toContain(route('accessibilite.plan-2026'))
        ->toContain(route('confidentialite'))
        ->toContain(route('frontend.modules.index'));
});

it('includes publicly listable modules but excludes trainer-authored or inactive ones', function () {
    $public = makeSitemapTestModule();
    $trainerAuthored = makeSitemapTestModule(['is_trainer_authored' => true]);
    $inactive = makeSitemapTestModule(['status' => 0]);

    $response = $this->get('/sitemap.xml');

    $publicUrl = route('frontend.modules.show', ['category' => $public->category_id, 'module' => $public->id]);
    $trainerUrl = route('frontend.modules.show', ['category' => $trainerAuthored->category_id, 'module' => $trainerAuthored->id]);
    $inactiveUrl = route('frontend.modules.show', ['category' => $inactive->category_id, 'module' => $inactive->id]);

    $response->assertOk();
    expect($response->getContent())
        ->toContain($publicUrl)
        ->not->toContain($trainerUrl)
        ->not->toContain($inactiveUrl);
});
