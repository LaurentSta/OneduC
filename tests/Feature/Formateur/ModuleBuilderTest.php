<?php

use App\Models\Category;
use App\Models\Group;
use App\Models\Module;
use App\Models\ModuleSection;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

it('lets a trainer create, edit and assign a self-authored module, and renders its blocks for a trainee', function () {
    Storage::fake('public');

    $formateur = User::factory()->create(['role' => 'formateur']);
    $other = User::factory()->create(['role' => 'formateur']);
    $stagiaire = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $group = Group::query()->create([
        'name' => 'Groupe verif ' . uniqid(),
        'instructor_id' => $formateur->id,
    ]);

    DB::table('group_user')->insert([
        'group_id' => $group->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
    ]);

    $indexResponse = $this->actingAs($formateur)->get(route('formateur.modules.builder.index'));
    $indexResponse->assertOk();

    $storeResponse = $this->actingAs($formateur)->post(route('formateur.modules.builder.store'), [
        'module_title' => 'Module test verif',
        'description' => 'Description test',
    ]);

    $module = Module::where('module_title', 'Module test verif')->firstOrFail();
    $storeResponse->assertRedirect(route('formateur.modules.builder.edit', $module));

    $sectionResponse = $this->actingAs($formateur)->post(route('formateur.modules.builder.sections.store', $module), [
        'section_title' => 'Section test',
    ]);
    $sectionResponse->assertRedirect();

    $section = $module->sections()->firstOrFail();

    // Upload an image via the dedicated endpoint, as the block editor would.
    $uploadResponse = $this->actingAs($formateur)->post(
        route('formateur.modules.builder.images.store', $module),
        ['image' => UploadedFile::fake()->image('photo.jpg')]
    );
    $uploadResponse->assertOk();
    $imagePath = $uploadResponse->json('path');
    expect($imagePath)->toStartWith("modules_formateur/module_{$module->id}/images/");

    $blocks = [
        ['type' => 'text', 'html' => '<p>Bonjour <strong>monde</strong></p><script>alert(1)</script>'],
        ['type' => 'image', 'path' => $imagePath, 'caption' => '<b>Legende</b>'],
        ['type' => 'list', 'style' => 'bullet', 'items' => ['<i>item un</i>', 'item deux']],
        ['type' => 'quote', 'text' => '<script>alert(2)</script>Citation test', 'source' => 'Auteur'],
        ['type' => 'divider'],
        // A path outside this module's upload folder must be rejected.
        ['type' => 'image', 'path' => 'modules_formateur/module_999/images/hack.jpg', 'caption' => ''],
    ];

    $lectureResponse = $this->actingAs($formateur)->post(route('formateur.modules.builder.lectures.store', $section), [
        'lecture_title' => 'Lecon test',
        'content_blocks' => json_encode($blocks),
    ]);
    $lectureResponse->assertRedirect();

    $lecture = $section->lectures()->firstOrFail();
    expect($lecture->content_type)->toBe('blocks');

    $saved = collect($lecture->content_blocks);
    expect($saved)->toHaveCount(5); // the out-of-scope image block must have been dropped

    $textBlock = $saved->firstWhere('type', 'text');
    expect($textBlock['html'])->toContain('<strong>monde</strong>');
    expect($textBlock['html'])->not->toContain('<script>');

    $imageBlock = $saved->firstWhere('type', 'image');
    expect($imageBlock['path'])->toBe($imagePath);
    expect($imageBlock['caption'])->not->toContain('<b>');

    $listBlock = $saved->firstWhere('type', 'list');
    expect($listBlock['items'][0])->not->toContain('<i>');

    $quoteBlock = $saved->firstWhere('type', 'quote');
    expect($quoteBlock['text'])->not->toContain('<script>');
    expect($quoteBlock['text'])->toContain('Citation test');

    // Autre formateur : pas d'accès
    $this->actingAs($other)->get(route('formateur.modules.builder.edit', $module))->assertForbidden();

    // Assignation au groupe
    $assignResponse = $this->actingAs($formateur)->put(route('formateur.modules.builder.groups.sync', $module), [
        'group_ids' => [$group->id],
    ]);
    $assignResponse->assertRedirect();
    expect($module->groups()->pluck('groups.id')->all())->toContain($group->id);

    // Edition page loads with the block editor mount point
    $editResponse = $this->actingAs($formateur)->get(route('formateur.modules.builder.edit', $module));
    $editResponse->assertOk();
    $editResponse->assertSee('data-block-editor', false);

    // Rendu stagiaire
    $lectureUrl = route('stagiaire.module.lecture', [
        'module' => $module->id,
        'section' => $section->id,
        'lecture' => $lecture->id,
    ]);

    $stagiaireResponse = $this->actingAs($stagiaire)->get($lectureUrl);
    $stagiaireResponse->assertOk();
    $stagiaireResponse->assertSee('monde', false);
    $stagiaireResponse->assertSee('item deux', false);
    $stagiaireResponse->assertSee('Citation test', false);
});

it('lets a trainer duplicate a catalog module into an editable copy, preserving scorm lessons and syncing groups', function () {
    $formateur = User::factory()->create(['role' => 'formateur']);

    $category = Category::query()->create([
        'category_name' => 'Categorie dup ' . uniqid(),
        'category_slug' => 'categorie-dup-' . uniqid(),
    ]);
    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie dup',
        'subcategory_slug' => 'sous-categorie-dup-' . uniqid(),
    ]);

    $group = Group::query()->create([
        'name' => 'Groupe dup ' . uniqid(),
        'instructor_id' => $formateur->id,
    ]);

    // Catalog module authored by admin, with this trainer merely assigned as its teacher.
    $catalogModule = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Formation catalogue',
        'module_name' => 'Formation catalogue',
        'module_name_slug' => 'formation-catalogue-' . uniqid(),
        'status' => 1,
        'is_trainer_authored' => false,
    ]);

    DB::table('group_module')->insert([
        'group_id' => $group->id,
        'module_id' => $catalogModule->id,
    ]);

    $section = ModuleSection::query()->create([
        'module_id' => $catalogModule->id,
        'section_title' => 'Chapitre scorm',
    ]);

    $scormLecture = $section->lectures()->create([
        'module_id' => $catalogModule->id,
        'lecture_title' => 'Lecon scorm',
        'content_type' => 'scorm',
        'scorm_path' => 'modules/00_Lecons/demo/index.html',
        'position' => 1,
    ]);

    // Being assigned as formateur_id on a catalog module must NOT grant edit rights via the builder.
    $this->actingAs($formateur)
        ->put(route('formateur.modules.builder.update', $catalogModule), ['module_title' => 'Hack'])
        ->assertForbidden();

    $duplicateResponse = $this->actingAs($formateur)->post(route('formateur.modules.builder.duplicate', $catalogModule));

    $copy = Module::where('formateur_id', $formateur->id)->where('is_trainer_authored', true)->firstOrFail();
    $duplicateResponse->assertRedirect(route('formateur.modules.builder.edit', $copy));

    expect($copy->id)->not->toBe($catalogModule->id);
    expect($copy->module_title)->toContain('Formation catalogue');
    expect($copy->groups()->pluck('groups.id')->all())->toContain($group->id);

    $copiedSection = $copy->sections()->firstOrFail();
    $copiedLecture = $copiedSection->lectures()->firstOrFail();
    expect($copiedLecture->content_type)->toBe('scorm');
    expect($copiedLecture->scorm_path)->toBe($scormLecture->scorm_path);
    expect($copiedLecture->id)->not->toBe($scormLecture->id);

    // The trainer can now freely edit the copy (title) via the builder.
    $this->actingAs($formateur)
        ->put(route('formateur.modules.builder.update', $copy), ['module_title' => 'Titre personnalise'])
        ->assertRedirect();
    expect($copy->fresh()->module_title)->toBe('Titre personnalise');
});
