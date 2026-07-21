<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupQuizSessionAnswer extends Model
{
    protected $fillable = [
        'group_quiz_session_question_id',
        'user_id',
        'answer_option_ids',
        'given_answer',
        'is_correct',
        'answered_at',
    ];

    protected $casts = [
        'answer_option_ids' => 'array',
        'given_answer' => 'array',
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function sessionQuestion(): BelongsTo
    {
        return $this->belongsTo(GroupQuizSessionQuestion::class, 'group_quiz_session_question_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
