<?php

use App\Models\ModuleLecture;
use App\Models\User;
use App\Services\Scorm\ScormImporter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function createLectureForScormImport(User $admin): ModuleLecture
{
    $now = now();
    $suffix = Str::lower((string) Str::uuid());

    $categoryId = DB::table('categories')->insertGetId([
        'category_name' => 'Cat ' . $suffix,
        'category_description' => null,
        'category_slug' => 'cat-' . $suffix,
        'category_image' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $subcategoryId = DB::table('subcategories')->insertGetId([
        'category_id' => $categoryId,
        'subcategory_name' => 'Subcat ' . $suffix,
        'subcategory_description' => null,
        'subcategory_slug' => 'subcat-' . $suffix,
        'subcategory_image' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $moduleId = DB::table('modules')->insertGetId([
        'category_id' => $categoryId,
        'subcategory_id' => $subcategoryId,
        'formateur_id' => $admin->id,
        'module_title' => 'Module ' . $suffix,
        'module_name' => 'module-' . $suffix,
        'module_name_slug' => 'module-' . $suffix,
        'is_trainer_authored' => 0,
        'catalogue_key' => (string) Str::uuid(),
        'publication_state' => 'draft',
        'status' => 0,
        'certificat' => 0,
        'bestseller' => 0,
        'vedette' => 0,
        'surevalue' => 0,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $sectionId = DB::table('module_sections')->insertGetId([
        'module_id' => $moduleId,
        'section_title' => 'Section ' . $suffix,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $lectureId = DB::table('module_lectures')->insertGetId([
        'module_id' => $moduleId,
        'section_id' => $sectionId,
        'position' => 1,
        'lecture_title' => 'Lecture ' . $suffix,
        'slide_count' => 0,
        'question_count' => 0,
        'quiz_enabled' => 0,
        'quiz_questions_per_attempt' => 0,
        'use_active_scorm_version' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    return ModuleLecture::query()->findOrFail($lectureId);
}

it('imports lecture scorm into modules/00_Lecons and stores new scorm_path', function () {
    $admin = User::factory()->create([
        'prenom' => 'Admin',
        'role' => 'admin',
        'status' => true,
    ]);

    $lecture = createLectureForScormImport($admin);
    $expectedTargetFolder = "modules/00_Lecons/lecture_{$lecture->id}";
    $expectedIndexPath = $expectedTargetFolder . '/index_lms.html';

    $importer = \Mockery::mock(ScormImporter::class);
    $importer->shouldReceive('importToFolder')
        ->once()
        ->withArgs(function ($zipFile, $targetPath) use ($expectedTargetFolder) {
            return $zipFile instanceof UploadedFile
                && $targetPath === $expectedTargetFolder;
        })
        ->andReturn((object) [
            'package_id' => null,
            'version_id' => null,
            'relative_index_path' => $expectedIndexPath,
        ]);

    $this->app->instance(ScormImporter::class, $importer);

    $response = $this->actingAs($admin)
        ->from(route('admin.lectures.edit', $lecture->id))
        ->post(route('admin.scorm.import'), [
            'lecture_id' => $lecture->id,
            'zip' => UploadedFile::fake()->create('scorm.zip', 120, 'application/zip'),
        ]);

    $response->assertRedirect(route('admin.lectures.edit', $lecture->id));
    $response->assertSessionHas('new_scorm_path', $expectedIndexPath);

    $lecture->refresh();

    expect($lecture->scorm_path)->toBe($expectedIndexPath);
});
