<?php

use App\Models\Category;
use App\Models\Group;
use App\Models\Module;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('aggregates modules_count and total_site_time per group without a query per group', function () {
    $formateur = User::factory()->create(['role' => 'formateur']);

    $category = Category::query()->create([
        'category_name' => 'Categorie progression',
        'category_slug' => 'categorie-progression-'.uniqid(),
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie progression',
        'subcategory_slug' => 'sous-categorie-progression-'.uniqid(),
    ]);

    $groups = collect(range(1, 3))->map(function (int $i) use ($formateur, $category, $subcategory) {
        $group = Group::query()->create([
            'name' => "Groupe progression {$i} ".uniqid(),
            'description' => 'Groupe de test',
            'instructor_id' => $formateur->id,
        ]);

        $stagiaireA = User::factory()->create(['role' => 'stagiaire', 'total_site_time' => 100 * $i]);
        $stagiaireB = User::factory()->create(['role' => 'stagiaire', 'total_site_time' => 50 * $i]);

        $group->students()->attach([$stagiaireA->id, $stagiaireB->id], ['role_in_group' => 'stagiaire']);

        foreach (range(1, $i) as $moduleIndex) {
            $module = Module::query()->create([
                'category_id' => $category->id,
                'subcategory_id' => $subcategory->id,
                'formateur_id' => $formateur->id,
                'module_title' => "Module {$i}-{$moduleIndex}",
                'module_name' => "Module {$i}-{$moduleIndex}",
                'module_name_slug' => "module-{$i}-{$moduleIndex}-".uniqid(),
                'status' => 1,
            ]);

            DB::table('group_module')->insert([
                'group_id' => $group->id,
                'module_id' => $module->id,
            ]);
        }

        return $group;
    });

    DB::enableQueryLog();

    $response = $this->actingAs($formateur)->get(route('formateur.progressions.groupes'));

    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    $response->assertOk();

    $groupsById = $response->viewData('groupes')->getCollection()->keyBy('id');

    expect((int) $groupsById->get($groups[0]->id)->modules_count)->toBe(1);
    expect((int) $groupsById->get($groups[0]->id)->total_site_time)->toBe(150);

    expect((int) $groupsById->get($groups[1]->id)->modules_count)->toBe(2);
    expect((int) $groupsById->get($groups[1]->id)->total_site_time)->toBe(300);

    expect((int) $groupsById->get($groups[2]->id)->modules_count)->toBe(3);
    expect((int) $groupsById->get($groups[2]->id)->total_site_time)->toBe(450);

    // 3 groupes affiches : sans le N+1, le nombre de requetes ne doit pas
    // scaler avec le nombre de groupes (l'ancien code faisait 2 requetes
    // supplementaires par groupe).
    expect($queryCount)->toBeLessThan(34);
});
