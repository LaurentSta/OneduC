<?php

use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware();
    Storage::fake('public');
});

function createAdminForModuleVideo(): User
{
    return User::query()->create([
        'prenom' => 'Admin',
        'name' => 'Video Manager',
        'username' => 'admin.video.' . Str::random(6),
        'email' => 'admin.video.' . Str::random(6) . '@example.test',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'status' => true,
    ]);
}

function createFormateurForModuleVideo(): User
{
    return User::query()->create([
        'prenom' => 'Formateur',
        'name' => 'Presentation',
        'username' => 'formateur.video.' . Str::random(6),
        'email' => 'formateur.video.' . Str::random(6) . '@example.test',
        'password' => Hash::make('password'),
        'role' => 'formateur',
        'status' => true,
    ]);
}

function createCategoryForModuleVideo(): int
{
    return \DB::table('categories')->insertGetId([
        'category_name' => 'Bureautique',
        'category_slug' => 'bureautique-' . Str::random(6),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function createSubcategoryForModuleVideo(int $categoryId): int
{
    return \DB::table('subcategories')->insertGetId([
        'category_id' => $categoryId,
        'subcategory_name' => 'Word',
        'subcategory_slug' => 'word-' . Str::random(6),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('stores an uploaded module video in the public module videos folder', function () {
    $admin = createAdminForModuleVideo();
    $formateur = createFormateurForModuleVideo();
    $categoryId = createCategoryForModuleVideo();
    $subcategoryId = createSubcategoryForModuleVideo($categoryId);

    $response = $this->actingAs($admin)->post(route('admin.modules.store'), [
        'module_name' => 'word_debutant_01',
        'module_title' => 'Word Debutant',
        'formateur_id' => $formateur->id,
        'category_id' => $categoryId,
        'subcategory_id' => $subcategoryId,
        'certificat' => '1',
        'status' => '1',
        'module_video_file' => UploadedFile::fake()->create('intro-module.mp4', 2048, 'video/mp4'),
    ]);

    $response->assertRedirect(route('admin.modules'));

    $module = Module::query()->firstOrFail();

    expect($module->module_video)->not->toBeNull();
    expect($module->module_video)->toStartWith('/media/storage/modules/videos/modules/module_' . $module->id . '/');
    expect(Storage::disk('public')->exists(Str::after($module->module_video, '/media/storage/')))->toBeTrue();
    expect($module->publication_state)->toBe(Module::PUBLICATION_DRAFT);
    expect($module->status)->toBeFalse();
});

it('replaces the previous uploaded module video during update', function () {
    $admin = createAdminForModuleVideo();
    $formateur = createFormateurForModuleVideo();
    $categoryId = createCategoryForModuleVideo();
    $subcategoryId = createSubcategoryForModuleVideo($categoryId);

    $module = Module::query()->create([
        'category_id' => $categoryId,
        'subcategory_id' => $subcategoryId,
        'formateur_id' => $formateur->id,
        'module_name' => 'excel_debutant_01',
        'module_name_slug' => 'excel-debutant-01',
        'module_title' => 'Excel Debutant',
        'certificat' => true,
        'is_trainer_authored' => false,
        'publication_state' => Module::PUBLICATION_DRAFT,
        'status' => false,
    ]);

    $oldStoragePath = 'modules/videos/modules/module_' . $module->id . '/ancienne-video.mp4';
    $module->update([
        'module_video' => route('media.storage', ['path' => $oldStoragePath], false),
    ]);
    Storage::disk('public')->put($oldStoragePath, 'old-video');

    $response = $this->actingAs($admin)->put(route('admin.modules.update', $module->id), [
        'module_name' => $module->module_name,
        'module_title' => $module->module_title,
        'formateur_id' => $formateur->id,
        'category_id' => $categoryId,
        'subcategory_id' => $subcategoryId,
        'certificat' => '1',
        'module_video' => $module->module_video,
        'module_video_file' => UploadedFile::fake()->create('nouvelle-video.webm', 3072, 'video/webm'),
    ]);

    $response->assertRedirect(route('admin.modules'));

    $module->refresh();

    expect($module->module_video)->not->toBe(route('media.storage', ['path' => $oldStoragePath], false));
    expect($module->module_video)->toStartWith('/media/storage/modules/videos/modules/module_' . $module->id . '/');
    expect(Storage::disk('public')->exists($oldStoragePath))->toBeFalse();
    expect(Storage::disk('public')->exists(Str::after($module->module_video, '/media/storage/')))->toBeTrue();
});
