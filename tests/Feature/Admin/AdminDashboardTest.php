<?php

use App\Models\Category;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\ScormResult;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('loads the admin dashboard with a query count that does not scale with scorm results', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $category = Category::query()->create([
        'category_name' => 'Categorie dashboard',
        'category_slug' => 'categorie-dashboard-'.uniqid(),
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie dashboard',
        'subcategory_slug' => 'sous-categorie-dashboard-'.uniqid(),
    ]);

    $formateur = User::factory()->create(['role' => 'formateur']);

    $module = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module dashboard',
        'module_name' => 'Module dashboard',
        'module_name_slug' => 'module-dashboard-'.uniqid(),
        'status' => 1,
    ]);

    $section = ModuleSection::query()->create([
        'module_id' => $module->id,
        'section_title' => 'Chapitre',
    ]);

    // Cree plusieurs lecons avec des resultats scorm : l'ancien code faisait
    // 1 + 2N requetes ici (N = nombre de couples user/lecture distincts).
    foreach (range(1, 5) as $i) {
        $lecture = ModuleLecture::query()->create([
            'module_id' => $module->id,
            'section_id' => $section->id,
            'lecture_title' => "Lecon scorm {$i}",
            'position' => $i,
        ]);

        $stagiaire = User::factory()->create(['role' => 'stagiaire']);

        ScormResult::query()->create([
            'user_id' => $stagiaire->id,
            'lecture_id' => $lecture->id,
            'scorm_key' => 'cmi.core.lesson_status',
            'scorm_value' => 'completed',
        ]);
    }

    DB::enableQueryLog();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    $response->assertOk();
    // Avec le code mort supprime, le nombre de requetes reste constant
    // quel que soit le nombre de resultats scorm (pas de N+1).
    expect($queryCount)->toBeLessThan(25);
});
