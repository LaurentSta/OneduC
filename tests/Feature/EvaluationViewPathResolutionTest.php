<?php

use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

it('renders evaluation iframe from new evaluations path when file exists', function () {
    $user = User::factory()->create([
        'prenom' => 'Stagiaire',
        'role' => 'stagiaire',
        'status' => true,
    ]);

    $folder = 'eval_' . Str::lower((string) Str::uuid());
    $relative = "modules/evaluations/scorm/{$folder}/res/index.html";
    $absolute = public_path($relative);

    File::ensureDirectoryExists(dirname($absolute));
    File::put($absolute, '<html></html>');

    try {
        $evaluation = Evaluation::create([
            'titre' => 'Evaluation test',
            'scorm_path' => $folder,
        ]);

        $response = $this->actingAs($user)->get(route('evaluation.show', $evaluation->id));

        $response->assertOk();
        $response->assertSee(asset($relative), false);
    } finally {
        File::delete($absolute);
    }
});

it('renders evaluation iframe from legacy path when new path is missing', function () {
    $user = User::factory()->create([
        'prenom' => 'Stagiaire',
        'role' => 'stagiaire',
        'status' => true,
    ]);

    $folder = 'eval_' . Str::lower((string) Str::uuid());
    $relative = "modules/scorm/01_evaluations/{$folder}/res/index.html";
    $absolute = public_path($relative);

    File::ensureDirectoryExists(dirname($absolute));
    File::put($absolute, '<html></html>');

    try {
        $evaluation = Evaluation::create([
            'titre' => 'Evaluation test legacy',
            'scorm_path' => $folder,
        ]);

        $response = $this->actingAs($user)->get(route('evaluation.show', $evaluation->id));

        $response->assertOk();
        $response->assertSee(asset($relative), false);
    } finally {
        File::delete($absolute);
    }
});
