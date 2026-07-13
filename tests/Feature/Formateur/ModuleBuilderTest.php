<?php

use App\Models\Category;
use App\Models\Group;
use App\Models\Module;
use App\Models\ModuleLecture;
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
        'name' => 'Groupe verif '.uniqid(),
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

    // A freshly created module already carries its example structure (2 chapters, 2+1 lessons),
    // so the chapter/lesson under test must be looked up by title, not assumed to be the first one.
    $section = $module->sections()->where('section_title', 'Section test')->firstOrFail();

    // Upload an image via the dedicated endpoint, as the block editor would.
    $uploadResponse = $this->actingAs($formateur)->post(
        route('formateur.modules.builder.images.store', $module),
        ['image' => UploadedFile::fake()->image('photo.jpg')]
    );
    $uploadResponse->assertOk();
    $mediaId = $uploadResponse->json('media_id');
    $mediaUrl = $uploadResponse->json('url');
    expect($mediaId)->toBeInt();
    expect($mediaUrl)->toBeString();

    $blocks = [
        ['type' => 'text', 'html' => '<p>Bonjour <strong>monde</strong></p><script>alert(1)</script>'],
        ['type' => 'image', 'media_id' => $mediaId, 'caption' => '<b>Legende</b>'],
        ['type' => 'quote', 'text' => '<script>alert(2)</script>Citation test', 'source' => 'Auteur'],
        ['type' => 'divider'],
        ['type' => 'divider', 'mode' => 'reveal', 'label' => '<script>alert(3)</script>Voir la correction'],
        ['type' => 'divider', 'mode' => 'not-a-real-mode'],
        // A media_id that does not belong to this module's media library entries must be rejected.
        ['type' => 'image', 'media_id' => 999999, 'caption' => ''],
    ];

    $lectureResponse = $this->actingAs($formateur)->post(route('formateur.modules.builder.lectures.store', $section), [
        'lecture_title' => 'Lecon test',
        'content_blocks' => json_encode($blocks),
    ]);
    $lectureResponse->assertRedirect();

    $lecture = $section->lectures()->where('lecture_title', 'Lecon test')->firstOrFail();
    expect($lecture->content_type)->toBe('blocks');

    $saved = collect($lecture->content_blocks);
    expect($saved)->toHaveCount(6); // the out-of-scope image block must have been dropped

    $textBlock = $saved->firstWhere('type', 'text');
    expect($textBlock['html'])->toContain('<strong>monde</strong>');
    expect($textBlock['html'])->not->toContain('<script>');

    $imageBlock = $saved->firstWhere('type', 'image');
    expect($imageBlock['media_id'])->toBe($mediaId);
    expect($imageBlock['caption'])->not->toContain('<b>');

    $quoteBlock = $saved->firstWhere('type', 'quote');
    expect($quoteBlock['text'])->not->toContain('<script>');
    expect($quoteBlock['text'])->toContain('Citation test');

    $dividers = $saved->where('type', 'divider')->values();
    expect($dividers)->toHaveCount(3);
    expect($dividers[0])->toBe(['type' => 'divider', 'mode' => 'simple']); // legacy divider with no mode key at all
    expect($dividers[1])->toBe(['type' => 'divider', 'mode' => 'reveal']); // stray 'label' input must be dropped, not persisted
    expect($dividers[2])->toBe(['type' => 'divider', 'mode' => 'simple']); // invalid mode must be coerced back to 'simple'

    // Autre formateur : pas d'accès
    $this->actingAs($other)->get(route('formateur.modules.builder.edit', $module))->assertForbidden();

    // Assignation au groupe
    $assignResponse = $this->actingAs($formateur)->put(route('formateur.modules.builder.groups.sync', $module), [
        'group_ids' => [$group->id],
    ]);
    $assignResponse->assertRedirect();
    expect($module->groups()->pluck('groups.id')->all())->toContain($group->id);

    // Edition page loads with the continuous outline mount point.
    $editResponse = $this->actingAs($formateur)->get(route('formateur.modules.builder.edit', $module));
    $editResponse->assertOk();
    $editResponse->assertSee('data-outline-editor', false);

    // The dedicated lesson page still mounts the block editor and is reachable from the outline.
    $lectureEditResponse = $this->actingAs($formateur)->get(route('formateur.modules.builder.lectures.edit', $lecture));
    $lectureEditResponse->assertOk();
    $lectureEditResponse->assertSee('data-block-editor', false);

    // A foreign trainer cannot open the dedicated lesson page either.
    $this->actingAs($other)->get(route('formateur.modules.builder.lectures.edit', $lecture))->assertForbidden();

    // Rendu stagiaire
    $lectureUrl = route('stagiaire.module.lecture', [
        'module' => $module->id,
        'section' => $section->id,
        'lecture' => $lecture->id,
    ]);

    $stagiaireResponse = $this->actingAs($stagiaire)->get($lectureUrl);
    $stagiaireResponse->assertOk();
    $stagiaireResponse->assertSee('monde', false);
    $stagiaireResponse->assertSee('Citation test', false);
});

it('autosaves the module title and description via JSON PUT without redirecting', function () {
    $formateur = User::factory()->create(['role' => 'formateur']);

    $storeResponse = $this->actingAs($formateur)->post(route('formateur.modules.builder.store'), [
        'module_title' => 'Module autosave',
        'description' => 'Description initiale',
    ]);

    $module = Module::where('module_title', 'Module autosave')->firstOrFail();
    $storeResponse->assertRedirect(route('formateur.modules.builder.edit', $module));

    // The builder's title/description field sends a JSON PUT (fetch) and must get a JSON
    // response back, not a redirect: a redirect response confuses the fetch caller into
    // reporting a save failure even though the write succeeded.
    $autosave = $this->actingAs($formateur)->putJson(route('formateur.modules.builder.update', $module), [
        'module_title' => 'Module autosave renomme',
        'description' => 'Nouvelle description',
    ]);

    $autosave->assertOk();
    expect($module->fresh()->module_title)->toBe('Module autosave renomme');
    expect($module->fresh()->description)->toBe('Nouvelle description');
});

it('lets a trainer duplicate a catalog module into an editable copy, preserving scorm lessons and syncing groups', function () {
    $formateur = User::factory()->create(['role' => 'formateur']);

    $category = Category::query()->create([
        'category_name' => 'Categorie dup '.uniqid(),
        'category_slug' => 'categorie-dup-'.uniqid(),
    ]);
    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie dup',
        'subcategory_slug' => 'sous-categorie-dup-'.uniqid(),
    ]);

    $group = Group::query()->create([
        'name' => 'Groupe dup '.uniqid(),
        'instructor_id' => $formateur->id,
    ]);

    // Catalog module authored by admin, with this trainer merely assigned as its teacher.
    $catalogModule = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Formation catalogue',
        'module_name' => 'Formation catalogue',
        'module_name_slug' => 'formation-catalogue-'.uniqid(),
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

it('lets a trainer reorder chapters and lessons via JSON, autosave a lesson, and blocks foreign modules', function () {
    $formateur = User::factory()->create(['role' => 'formateur']);
    $other = User::factory()->create(['role' => 'formateur']);

    $category = Category::query()->create([
        'category_name' => 'Categorie reorder '.uniqid(),
        'category_slug' => 'categorie-reorder-'.uniqid(),
    ]);
    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie reorder',
        'subcategory_slug' => 'sous-categorie-reorder-'.uniqid(),
    ]);

    $module = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module reorder',
        'module_name' => 'Module reorder',
        'module_name_slug' => 'module-reorder-'.uniqid(),
        'status' => 1,
        'is_trainer_authored' => true,
    ]);

    $sectionA = $module->sections()->create(['section_title' => 'Chapitre A', 'position' => 0]);
    $sectionB = $module->sections()->create(['section_title' => 'Chapitre B', 'position' => 1]);

    // storeSection responds with JSON when asked, and assigns the next position.
    $jsonStore = $this->actingAs($formateur)->postJson(
        route('formateur.modules.builder.sections.store', $module),
        ['section_title' => 'Chapitre C']
    );
    $jsonStore->assertCreated();
    $sectionC = ModuleSection::where('section_title', 'Chapitre C')->firstOrFail();
    expect($sectionC->position)->toBe(2);

    // Reordering to C, A, B must persist the new positions.
    $reorderSections = $this->actingAs($formateur)->postJson(
        route('formateur.modules.builder.sections.reorder', $module),
        ['section_ids' => [$sectionC->id, $sectionA->id, $sectionB->id]]
    );
    $reorderSections->assertOk()->assertJson(['success' => true]);
    expect($sectionC->fresh()->position)->toBe(0);
    expect($sectionA->fresh()->position)->toBe(1);
    expect($sectionB->fresh()->position)->toBe(2);

    // A reorder payload missing a lesson (or from a foreign module) must be rejected.
    $this->actingAs($formateur)->postJson(
        route('formateur.modules.builder.sections.reorder', $module),
        ['section_ids' => [$sectionA->id, $sectionB->id]]
    )->assertStatus(422);

    $lecture1 = $sectionA->lectures()->create([
        'module_id' => $module->id, 'lecture_title' => 'Lecon 1', 'content_type' => 'blocks', 'content_blocks' => [], 'position' => 0,
    ]);
    $lecture2 = $sectionA->lectures()->create([
        'module_id' => $module->id, 'lecture_title' => 'Lecon 2', 'content_type' => 'blocks', 'content_blocks' => [], 'position' => 1,
    ]);

    $reorderLectures = $this->actingAs($formateur)->postJson(
        route('formateur.modules.builder.lectures.reorder', $sectionA),
        ['lecture_ids' => [$lecture2->id, $lecture1->id]]
    );
    $reorderLectures->assertOk()->assertJson(['success' => true]);
    expect($lecture2->fresh()->position)->toBe(0);
    expect($lecture1->fresh()->position)->toBe(1);

    // Autosave: a JSON PUT on the lecture returns the updated payload instead of redirecting.
    $autosave = $this->actingAs($formateur)->putJson(
        route('formateur.modules.builder.lectures.update', $lecture1),
        ['lecture_title' => 'Lecon 1 renommee', 'content_blocks' => json_encode([['type' => 'divider']])]
    );
    $autosave->assertOk()->assertJsonPath('lecture.lecture_title', 'Lecon 1 renommee');
    expect($lecture1->fresh()->lecture_title)->toBe('Lecon 1 renommee');
    expect($lecture1->fresh()->content_blocks)->toHaveCount(1);

    // A title-only rename (as the outline editor sends, with no content_blocks key at all)
    // must not wipe out content already saved by the block editor.
    $titleOnlyRename = $this->actingAs($formateur)->putJson(
        route('formateur.modules.builder.lectures.update', $lecture1),
        ['lecture_title' => 'Lecon 1 renommee encore']
    );
    $titleOnlyRename->assertOk();
    expect($lecture1->fresh()->lecture_title)->toBe('Lecon 1 renommee encore');
    expect($lecture1->fresh()->content_blocks)->toHaveCount(1);

    // A foreign trainer cannot reorder or autosave into someone else's module.
    $this->actingAs($other)->postJson(
        route('formateur.modules.builder.sections.reorder', $module),
        ['section_ids' => [$sectionA->id, $sectionB->id, $sectionC->id]]
    )->assertForbidden();

    $this->actingAs($other)->putJson(
        route('formateur.modules.builder.lectures.update', $lecture1),
        ['lecture_title' => 'Hack', 'content_blocks' => '[]']
    )->assertForbidden();
});

it('lets a trainer move a lesson across chapters and promote an empty lesson into a chapter', function () {
    $formateur = User::factory()->create(['role' => 'formateur']);
    $other = User::factory()->create(['role' => 'formateur']);

    $category = Category::query()->create([
        'category_name' => 'Categorie move '.uniqid(),
        'category_slug' => 'categorie-move-'.uniqid(),
    ]);
    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie move',
        'subcategory_slug' => 'sous-categorie-move-'.uniqid(),
    ]);

    $module = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module move',
        'module_name' => 'Module move',
        'module_name_slug' => 'module-move-'.uniqid(),
        'status' => 1,
        'is_trainer_authored' => true,
    ]);

    $sectionA = $module->sections()->create(['section_title' => 'Chapitre A', 'position' => 0]);
    $sectionB = $module->sections()->create(['section_title' => 'Chapitre B', 'position' => 1]);

    $lectureA1 = $sectionA->lectures()->create([
        'module_id' => $module->id, 'lecture_title' => 'Lecon A1', 'content_type' => 'blocks', 'content_blocks' => [], 'position' => 0,
    ]);
    $lectureA2 = $sectionA->lectures()->create([
        'module_id' => $module->id, 'lecture_title' => 'Lecon A2', 'content_type' => 'blocks', 'content_blocks' => [], 'position' => 1,
    ]);
    $lectureB1 = $sectionB->lectures()->create([
        'module_id' => $module->id, 'lecture_title' => 'Lecon B1', 'content_type' => 'blocks', 'content_blocks' => [], 'position' => 0,
    ]);

    // Move Lecon A2 to the front of Chapitre B.
    $moveResponse = $this->actingAs($formateur)->postJson(
        route('formateur.modules.builder.lectures.move', $lectureA2),
        ['section_id' => $sectionB->id, 'position' => 0]
    );
    $moveResponse->assertOk()->assertJsonPath('lecture.section_id', $sectionB->id);

    expect($lectureA2->fresh()->section_id)->toBe($sectionB->id);
    expect($lectureA2->fresh()->position)->toBe(0);
    expect($lectureB1->fresh()->position)->toBe(1);
    // The vacated chapter's remaining lesson is renumbered without a gap.
    expect($lectureA1->fresh()->position)->toBe(0);

    // A section_id from a foreign module is rejected.
    $foreignModule = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Autre module',
        'module_name' => 'Autre module',
        'module_name_slug' => 'autre-module-'.uniqid(),
        'status' => 1,
        'is_trainer_authored' => true,
    ]);
    $foreignSection = $foreignModule->sections()->create(['section_title' => 'Chapitre etranger', 'position' => 0]);

    $this->actingAs($formateur)->postJson(
        route('formateur.modules.builder.lectures.move', $lectureA1),
        ['section_id' => $foreignSection->id, 'position' => 0]
    )->assertStatus(422);

    // Promoting an empty lesson turns it into a new chapter and removes the lesson.
    $promoteResponse = $this->actingAs($formateur)->postJson(
        route('formateur.modules.builder.lectures.promote', $lectureB1)
    );
    $promoteResponse->assertCreated()->assertJsonPath('section.section_title', 'Lecon B1');
    expect(ModuleLecture::find($lectureB1->id))->toBeNull();
    expect($module->sections()->where('section_title', 'Lecon B1')->exists())->toBeTrue();

    // A lesson that already has content cannot be silently promoted (content would be lost).
    $lectureWithContent = $sectionA->lectures()->create([
        'module_id' => $module->id, 'lecture_title' => 'Lecon avec contenu', 'content_type' => 'blocks',
        'content_blocks' => [['type' => 'divider']], 'position' => 5,
    ]);
    $this->actingAs($formateur)->postJson(
        route('formateur.modules.builder.lectures.promote', $lectureWithContent)
    )->assertStatus(422);
    expect(ModuleLecture::find($lectureWithContent->id))->not->toBeNull();

    // A foreign trainer cannot move or promote lessons in someone else's module.
    $this->actingAs($other)->postJson(
        route('formateur.modules.builder.lectures.move', $lectureA1),
        ['section_id' => $sectionA->id, 'position' => 0]
    )->assertForbidden();

    $this->actingAs($other)->postJson(
        route('formateur.modules.builder.lectures.promote', $lectureA1)
    )->assertForbidden();
});
