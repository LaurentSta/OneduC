<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrueFalseSessionResponse extends Model
{
    protected $fillable = [
        'true_false_session_id',
        'user_id',
        'question_index',
        'answer',
    ];

    protected $casts = [
        'answer' => 'boolean',
    ];

    public function trueFalseSession(): BelongsTo
    {
        return $this->belongsTo(TrueFalseSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
