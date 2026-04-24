<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionWallVote extends Model
{
    protected $fillable = [
        'question_wall_question_id',
        'user_id',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionWallQuestion::class, 'question_wall_question_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
