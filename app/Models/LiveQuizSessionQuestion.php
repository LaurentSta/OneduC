<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveQuizSessionQuestion extends Model
{
    protected $fillable = [
        'live_quiz_session_id',
        'question_id',
        'position',
    ];

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
