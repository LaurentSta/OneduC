<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollSessionResponse extends Model
{
    protected $fillable = [
        'poll_session_id',
        'user_id',
        'question_index',
        'choice_index',
    ];

    public function pollSession(): BelongsTo
    {
        return $this->belongsTo(PollSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
