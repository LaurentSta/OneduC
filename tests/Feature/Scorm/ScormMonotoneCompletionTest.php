<?php

use App\Models\ContentBlockScormScore;
use App\Models\ScormScore;

test('a standalone scorm lecture is force-completed once the best score reaches the pass threshold', function () {
    ['memberStagiaire' => $stagiaire, 'lecture' => $lecture] = seedScormAccessContext();

    $this->actingAs($stagiaire)->post('/scorm/save-progress', [
        'lecture_id' => $lecture->id,
        'scorm_key' => 'cmi.core.score.raw',
        'scorm_value' => 60,
    ])->assertOk();

    $score = ScormScore::where('user_id', $stagiaire->id)->where('lecture_id', $lecture->id)->first();

    expect((bool) $score->is_completed)->toBeTrue();
    expect($score->lesson_status)->toBe('completed');
});

test('a scorm content block is force-completed once the best score reaches the pass threshold', function () {
    ['memberStagiaire' => $stagiaire, 'lecture' => $lecture] = seedScormAccessContext();

    $this->actingAs($stagiaire)->post('/scorm/save-block-progress', [
        'lecture_id' => $lecture->id,
        'content_block_key' => 'bloc-scorm-1',
        'scorm_key' => 'cmi.core.score.raw',
        'scorm_value' => 60,
    ])->assertOk();

    $score = ContentBlockScormScore::where('user_id', $stagiaire->id)
        ->where('lecture_id', $lecture->id)
        ->where('content_block_key', 'bloc-scorm-1')
        ->first();

    expect($score->is_completed)->toBeTrue();
    expect($score->lesson_status)->toBe('completed');
});
