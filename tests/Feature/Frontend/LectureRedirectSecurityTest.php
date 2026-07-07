<?php

use App\Models\Category;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\SubCategory;
use App\Models\User;

function createLectureForRedirectSecurityTest(): array
{
    $user = User::factory()->create([
        'role' => 'stagiaire',
        'status' => true,
        'password_changed_at' => now(),
    ]);

    $category = Category::query()->create([
        'category_name' => 'Categorie redirect '.uniqid(),
        'category_slug' => 'categorie-redirect-'.uniqid(),
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie redirect',
        'subcategory_slug' => 'sous-categorie-redirect-'.uniqid(),
    ]);

    $module = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $user->id,
        'module_title' => 'Module redirect',
        'module_name' => 'Module redirect',
        'module_name_slug' => 'module-redirect-'.uniqid(),
        'status' => true,
    ]);

    $section = ModuleSection::query()->create([
        'module_id' => $module->id,
        'section_title' => 'Chapitre redirect',
    ]);

    $lecture = ModuleLecture::query()->create([
        'module_id' => $module->id,
        'section_id' => $section->id,
        'lecture_title' => 'Lecon redirect',
        'position' => 1,
    ]);

    return compact('user', 'lecture');
}

it('does not redirect lesson validation to an external url', function () {
    ['user' => $user, 'lecture' => $lecture] = createLectureForRedirectSecurityTest();

    $token = 'csrf-lecture-redirect-external';

    $this->withSession(['_token' => $token])
        ->from('/formations')
        ->actingAs($user)
        ->post(route('lecture.valider', $lecture), [
            '_token' => $token,
            'redirect_to' => 'https://evil.example/phishing',
        ])
        ->assertRedirect('/formations');
});

it('allows lesson validation redirects to an internal path', function () {
    ['user' => $user, 'lecture' => $lecture] = createLectureForRedirectSecurityTest();

    $token = 'csrf-lecture-redirect-internal';

    $this->withSession(['_token' => $token])
        ->actingAs($user)
        ->post(route('lecture.valider', $lecture), [
            '_token' => $token,
            'redirect_to' => '/stagiaire/modules?done=1',
        ])
        ->assertRedirect('/stagiaire/modules?done=1');
});
