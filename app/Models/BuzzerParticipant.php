<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuzzerParticipant extends Model
{
    protected $fillable = [
        'buzzer_session_id',
        'user_id',
        'score',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(BuzzerSession::class, 'buzzer_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
