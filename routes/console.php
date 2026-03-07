<?php

use App\Models\QuizQuestion;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('quiz:cleanup-orphan-questions', function () {
    $deleted = 0;

    QuizQuestion::query()
        ->whereDoesntHave('lecture')
        ->orderBy('id')
        ->chunkById(200, function ($questions) use (&$deleted): void {
            foreach ($questions as $question) {
                if (!empty($question->image_path)) {
                    Storage::disk('public')->delete($question->image_path);
                }
                if (!empty($question->audio_path)) {
                    Storage::disk('public')->delete($question->audio_path);
                }
                $question->delete();
                $deleted++;
            }
        });

    $this->info("Questions orphelines supprimées: {$deleted}");
})->purpose('Delete quiz questions that are not linked to any lecture');
