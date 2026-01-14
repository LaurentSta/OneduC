<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttemptQuestion extends Model
{
    protected $fillable = [
        'attempt_id',
        'question_id',
        'position',
        'given_answer',
        'is_correct',
        'answered_at',
    ];

    protected $casts = [
        'given_answer' => 'array',
        'answered_at'  => 'datetime',
        'is_correct'   => 'boolean',
    ];

    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}
