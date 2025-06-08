<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScormEvaluationScore extends Model
{
    protected $fillable = [
        'user_id',
        'evaluation_id',
        'first_score',
        'last_score',
        'best_score',
        'attempts_count',
        'questions_answered',
        'session_time',
        'last_attempt_at',
        'lesson_status',
        'is_completed',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }
}
