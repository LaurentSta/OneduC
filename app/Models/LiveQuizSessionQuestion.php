<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveQuizSessionQuestion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'position' => 'integer',
    ];

    public function liveQuizSession(): BelongsTo
    {
        return $this->belongsTo(LiveQuizSession::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}
