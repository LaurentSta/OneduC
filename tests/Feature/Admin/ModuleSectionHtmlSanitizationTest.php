<?php

use App\Models\Category;
use App\Models\Module;
use App\Models\ModuleSection;
use App\Models\SubCategory;
use App\Models\User;

it('sanitizes chapter introduction html before rendering it to learners', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => true,
    ]);

    $category = Category::query()->create([
        'category_name' => 'Categorie securite '.uniqid(),
        'category_slug' => 'categorie-securite-'.uniqid(),
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie securite',
        'subcategory_slug' => 'sous-categorie-securite-'.uniqid(),
    ]);

    $module = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $admin->id,
        'module_title' => 'Module securite',
        'module_name' => 'Module securite',
        'module_name_slug' => 'module-securite-'.uniqid(),
        'status' => true,
    ]);

    $section = ModuleSection::query()->create([
        'module_id' => $module->id,
        'section_title' => 'Chapitre securite',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.sections.update', $section), [
        'section_title' => 'Chapitre securise',
        'section_html' => '<p onclick="alert(1)" style="color:red">Question <a href=javascript:alert(9)>piegee</a> <a href="https://example.test">ok</a></p><script>alert(2)</script>',
        'video_url' => '',
    ]);

    $response->assertRedirect();

    $html = $section->fresh()->section_html;

    expect($html)->toContain('<p>Question');
    expect($html)->toContain('<a>piegee</a>');
    expect($html)->toContain('href="https://example.test"');
    expect($html)->not->toContain('onclick');
    expect($html)->not->toContain('style=');
    expect($html)->not->toContain('javascript:');
    expect($html)->not->toContain('<script>');
});
