<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupQuizSessionQuestion extends Model
{
    protected $fillable = [
        'group_quiz_session_id',
        'question_id',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(GroupQuizSession::class, 'group_quiz_session_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(GroupQuizSessionAnswer::class);
    }
}
