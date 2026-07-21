<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupQuizSessionParticipant extends Model
{
    protected $fillable = [
        'group_quiz_session_id',
        'user_id',
        'joined_at',
        'last_seen_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(GroupQuizSession::class, 'group_quiz_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
