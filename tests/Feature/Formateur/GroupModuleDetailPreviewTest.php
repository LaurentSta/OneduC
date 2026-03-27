<?php

use App\Models\Category;
use App\Models\Group;
use App\Models\GroupModuleLecture;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('opens the group module detail before chapters from the lesson customization view', function () {
    $formateur = User::factory()->create([
        'role' => 'formateur',
    ]);

    $category = Category::query()->create([
        'category_name' => 'Categorie detail groupe',
        'category_slug' => 'categorie-detail-groupe',
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie detail groupe',
        'subcategory_slug' => 'sous-categorie-detail-groupe',
    ]);

    $group = Group::query()->create([
        'name' => 'Groupe detail preview ' . uniqid(),
        'description' => 'Groupe de test',
        'instructor_id' => $formateur->id,
    ]);

    $module = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module personnalise',
        'module_name' => 'Module personnalise',
        'module_name_slug' => 'module-personnalise',
        'status' => 1,
    ]);

    DB::table('group_module')->insert([
        'group_id' => $group->id,
        'module_id' => $module->id,
    ]);

    $hiddenSection = ModuleSection::query()->create([
        'module_id' => $module->id,
        'section_title' => 'Chapitre masque',
    ]);

    $visibleSection = ModuleSection::query()->create([
        'module_id' => $module->id,
        'section_title' => 'Chapitre visible',
    ]);

    $hiddenLecture = ModuleLecture::query()->create([
        'module_id' => $module->id,
        'section_id' => $hiddenSection->id,
        'lecture_title' => 'Lecon masquee',
        'position' => 1,
    ]);

    $visibleLecture = ModuleLecture::query()->create([
        'module_id' => $module->id,
        'section_id' => $visibleSection->id,
        'lecture_title' => 'Lecon visible',
        'position' => 1,
    ]);

    GroupModuleLecture::query()->create([
        'group_id' => $group->id,
        'module_id' => $module->id,
        'lecture_id' => $hiddenLecture->id,
        'position' => 1,
        'is_enabled' => false,
    ]);

    GroupModuleLecture::query()->create([
        'group_id' => $group->id,
        'module_id' => $module->id,
        'lecture_id' => $visibleLecture->id,
        'position' => 1,
        'is_enabled' => true,
    ]);

    $previewUrl = route('formateur.formations.detail', [
        'module' => $module->id,
        'mode' => 'groupe',
        'group_id' => $group->id,
    ]);

    $customizationResponse = $this->actingAs($formateur)
        ->get(route('formateur.groupes.modules.lecons.edit', [
            'group' => $group->id,
            'module' => $module->id,
        ]));

    $customizationResponse->assertOk();
    $customizationResponse->assertSee(htmlspecialchars($previewUrl, ENT_QUOTES, 'UTF-8'), false);

    $detailResponse = $this->actingAs($formateur)->get($previewUrl);

    $detailResponse->assertOk();
    $detailResponse->assertDontSee('Lecon masquee');
    $detailResponse->assertSee('Lecon visible');

    $sectionUrl = route('formateur.formations.section', [
        'module' => $module->id,
        'section' => $visibleSection->id,
    ]) . '?mode=groupe&amp;group_id=' . $group->id;

    $lectureUrl = route('formateur.formations.lecture', [
        'module' => $module->id,
        'section' => $visibleSection->id,
        'lecture' => $visibleLecture->id,
    ]) . '?mode=groupe&amp;group_id=' . $group->id;

    $detailResponse->assertSee($sectionUrl, false);
    $detailResponse->assertSee($lectureUrl, false);
});
