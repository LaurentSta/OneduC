<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuzzerAttempt extends Model
{
    protected $fillable = [
        'buzzer_session_id',
        'buzzer_question_id',
        'user_id',
        'reaction_ms',
        'result',
    ];

    protected $casts = [
        'reaction_ms' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(BuzzerSession::class, 'buzzer_session_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(BuzzerQuestion::class, 'buzzer_question_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
