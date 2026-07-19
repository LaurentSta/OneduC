<?php

use App\Models\Category;
use App\Models\Group;
use App\Models\Module;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

function contexteModuleConstructeurAdmin(array $attributs = []): array
{
    $categorie = Category::query()->create([
        'category_name' => 'Catégorie constructeur '.Str::random(6),
        'category_slug' => 'categorie-constructeur-'.Str::random(8),
    ]);
    $sousCategorie = SubCategory::query()->create([
        'category_id' => $categorie->id,
        'subcategory_name' => 'Sous-catégorie constructeur',
        'subcategory_slug' => 'sous-categorie-constructeur-'.Str::random(8),
    ]);

    $module = Module::query()->create(array_merge([
        'category_id' => $categorie->id,
        'subcategory_id' => $sousCategorie->id,
        'module_title' => 'Formation catalogue test',
        'module_name' => 'Formation catalogue test',
        'module_name_slug' => 'formation-catalogue-test-'.Str::random(6),
        'is_trainer_authored' => false,
        'publication_state' => Module::PUBLICATION_DRAFT,
        'status' => false,
    ], $attributs));

    return compact('categorie', 'sousCategorie', 'module');
}

it('permet à un administrateur de créer un brouillon catalogue minimal avec un référent facultatif', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('admin.formations.constructeur.store'), [
        'module_title' => 'Formation officielle vide',
        'description' => 'À construire',
    ]);

    $module = Module::query()->where('module_title', 'Formation officielle vide')->firstOrFail();

    $response->assertRedirect(route('admin.formations.constructeur.edit', $module));
    expect($module->is_trainer_authored)->toBeFalse()
        ->and($module->publication_state)->toBe(Module::PUBLICATION_DRAFT)
        ->and($module->status)->toBeFalse()
        ->and($module->created_by)->toBe($admin->id)
        ->and($module->formateur_id)->toBeNull()
        ->and($module->sections()->count())->toBe(1)
        ->and($module->sections()->first()->section_title)->toBe('Chapitre 1')
        ->and($module->lectures()->count())->toBe(0);
});

it('fait passer l’ancien formulaire par la validation de publication', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ['categorie' => $categorie, 'sousCategorie' => $sousCategorie, 'module' => $module] = contexteModuleConstructeurAdmin([
        'created_by' => $admin->id,
    ]);
    $payload = [
        'module_name' => $module->module_name,
        'module_title' => $module->module_title,
        'category_id' => $categorie->id,
        'subcategory_id' => $sousCategorie->id,
        'certificat' => '0',
        'status' => '1',
    ];
    $retour = route('admin.modules.edit', $module);

    $this->actingAs($admin)
        ->from($retour)
        ->put(route('admin.modules.update', $module), $payload)
        ->assertRedirect($retour)
        ->assertSessionHasErrors('publication');

    expect($module->fresh()->publication_state)->toBe(Module::PUBLICATION_DRAFT)
        ->and($module->fresh()->status)->toBeFalse();

    $section = $module->sections()->create(['section_title' => 'Chapitre complet', 'position' => 0]);
    $section->lectures()->create([
        'module_id' => $module->id,
        'lecture_title' => 'Leçon complète',
        'content_type' => 'blocks',
        'content_blocks' => [['type' => 'divider']],
        'position' => 0,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.modules.update', $module), $payload)
        ->assertRedirect(route('admin.modules'));

    expect($module->fresh()->publication_state)->toBe(Module::PUBLICATION_PUBLISHED)
        ->and($module->fresh()->status)->toBeTrue();
});

it('gère les chapitres puis affecte la version publiée aux groupes de tous les formateurs', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $formateurA = User::factory()->create(['role' => 'formateur']);
    $formateurB = User::factory()->create(['role' => 'formateur']);
    ['module' => $module] = contexteModuleConstructeurAdmin(['created_by' => $admin->id]);

    $groupeA = Group::query()->create(['name' => 'Groupe A', 'instructor_id' => $formateurA->id]);
    $groupeB = Group::query()->create(['name' => 'Groupe B', 'instructor_id' => $formateurB->id]);

    $chapitreA = $module->sections()->create(['section_title' => 'Chapitre A', 'position' => 0]);
    $chapitreB = $module->sections()->create(['section_title' => 'Chapitre B', 'position' => 1]);

    $creation = $this->actingAs($admin)->postJson(
        route('admin.formations.constructeur.lectures.store', $chapitreA),
        ['lecture_title' => 'Leçon administrateur', 'content_blocks' => json_encode([['type' => 'divider']])],
    );
    $creation->assertCreated();
    $lecture = $chapitreA->lectures()->where('lecture_title', 'Leçon administrateur')->firstOrFail();

    $this->actingAs($admin)->postJson(
        route('admin.formations.constructeur.lectures.move', $lecture),
        ['section_id' => $chapitreB->id, 'position' => 0],
    )->assertOk();

    $this->actingAs($admin)->postJson(
        route('admin.formations.constructeur.lectures.store', $chapitreA),
        ['lecture_title' => 'Leçon du chapitre A', 'content_blocks' => json_encode([['type' => 'divider']])],
    )->assertCreated();

    $this->actingAs($admin)
        ->post(route('admin.formations.constructeur.publish', $module))
        ->assertRedirect(route('admin.formations.constructeur.edit', $module));

    $this->actingAs($admin)->put(
        route('admin.formations.constructeur.groups.sync', $module),
        ['group_ids' => [$groupeA->id, $groupeB->id]],
    )->assertRedirect();

    expect($lecture->fresh()->section_id)->toBe($chapitreB->id)
        ->and($module->groups()->pluck('groups.id')->sort()->values()->all())
        ->toBe(collect([$groupeA->id, $groupeB->id])->sort()->values()->all())
        ->and($module->fresh()->publication_state)->toBe(Module::PUBLICATION_PUBLISHED);
});

it('autorise la consultation mais bloque toute mutation des versions publiées et des créations formateur', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $formateur = User::factory()->create(['role' => 'formateur']);

    ['module' => $publiee] = contexteModuleConstructeurAdmin([
        'created_by' => $admin->id,
        'publication_state' => Module::PUBLICATION_PUBLISHED,
        'published_at' => now(),
        'status' => true,
    ]);
    $section = $publiee->sections()->create(['section_title' => 'Chapitre publié', 'position' => 0]);
    $lecture = $section->lectures()->create([
        'module_id' => $publiee->id,
        'lecture_title' => 'Leçon publiée',
        'content_type' => 'blocks',
        'content_blocks' => [['type' => 'divider']],
        'position' => 0,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.formations.constructeur.edit', $publiee))
        ->assertOk();
    $this->actingAs($admin)
        ->get(route('admin.formations.constructeur.lectures.edit', $lecture))
        ->assertOk();
    $this->actingAs($admin)
        ->putJson(route('admin.formations.constructeur.update', $publiee), ['module_title' => 'Titre interdit'])
        ->assertForbidden();
    expect($publiee->fresh()->module_title)->not->toBe('Titre interdit');

    ['module' => $creationFormateur] = contexteModuleConstructeurAdmin([
        'formateur_id' => $formateur->id,
        'is_trainer_authored' => true,
        'publication_state' => Module::PUBLICATION_DRAFT,
        'status' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.formations.constructeur.edit', $creationFormateur))
        ->assertForbidden();
    $this->actingAs($admin)
        ->putJson(route('admin.formations.constructeur.update', $creationFormateur), ['module_title' => 'Titre piraté'])
        ->assertForbidden();
    expect($creationFormateur->fresh()->module_title)->not->toBe('Titre piraté');
});

it('bloque les mutations legacy chapitre leçon slides et scorm sur une version publiée', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ['module' => $publiee] = contexteModuleConstructeurAdmin([
        'created_by' => $admin->id,
        'publication_state' => Module::PUBLICATION_PUBLISHED,
        'published_at' => now(),
        'status' => true,
    ]);
    $section = $publiee->sections()->create(['section_title' => 'Chapitre immuable', 'position' => 0]);
    $lecture = $section->lectures()->create([
        'module_id' => $publiee->id,
        'lecture_title' => 'Leçon immuable',
        'content_type' => 'scorm',
        'position' => 0,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.sections.update', $section), [
            'section_title' => 'Chapitre modifié',
        ])
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('admin.lectures.update'), [
            'id' => $lecture->id,
            'lecture_title' => 'Leçon modifiée',
            'content_type' => 'scorm',
        ])
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('admin.slides.import'), [
            'lecture_id' => $lecture->id,
            'slides_file' => UploadedFile::fake()->create('support.pdf', 10, 'application/pdf'),
        ])
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('admin.scorm.import'), [
            'lecture_id' => $lecture->id,
            'zip' => UploadedFile::fake()->create('module-scorm.zip', 10, 'application/zip'),
        ])
        ->assertForbidden();

    expect($section->fresh()->section_title)->toBe('Chapitre immuable')
        ->and($lecture->fresh()->lecture_title)->toBe('Leçon immuable')
        ->and($lecture->fresh()->slides_source_path)->toBeNull()
        ->and($lecture->fresh()->scorm_path)->toBeNull();
});

it('duplique profondément une création formateur sans modifier son original', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $formateur = User::factory()->create(['role' => 'formateur']);
    ['module' => $original] = contexteModuleConstructeurAdmin([
        'formateur_id' => $formateur->id,
        'is_trainer_authored' => true,
        'status' => true,
    ]);
    $section = $original->sections()->create(['section_title' => 'Chapitre personnel', 'position' => 0]);
    $section->lectures()->create([
        'module_id' => $original->id,
        'lecture_title' => 'Leçon personnelle',
        'content_type' => 'blocks',
        'content_blocks' => [['type' => 'text', 'html' => '<p>Original intact</p>']],
        'position' => 0,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.formations.constructeur.duplicate', $original));
    $copie = Module::query()
        ->where('source_module_id', $original->id)
        ->where('is_trainer_authored', false)
        ->firstOrFail();

    $response->assertRedirect(route('admin.formations.constructeur.edit', $copie));
    expect($copie->publication_state)->toBe(Module::PUBLICATION_DRAFT)
        ->and($copie->status)->toBeFalse()
        ->and($copie->catalogue_key)->not->toBe($original->catalogue_key)
        ->and($copie->sections()->count())->toBe(1)
        ->and($copie->lectures()->count())->toBe(1)
        ->and($original->fresh()->is_trainer_authored)->toBeTrue()
        ->and($original->fresh()->module_title)->toBe('Formation catalogue test');
});
