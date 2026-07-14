<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('rejects an svg upload as a category image', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'category_name' => 'Categorie test',
            'category_description' => 'Description',
            'category_image' => UploadedFile::fake()->create('icone.svg', 10, 'image/svg+xml'),
        ]);

    $response->assertSessionHasErrors('category_image');
});

it('accepts a png upload as a category image', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'category_name' => 'Categorie test png',
            'category_description' => 'Description',
            'category_image' => UploadedFile::fake()->image('icone.png'),
        ]);

    $response->assertSessionDoesntHaveErrors('category_image');
});
