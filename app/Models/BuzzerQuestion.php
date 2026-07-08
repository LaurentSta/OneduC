<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuzzerQuestion extends Model
{
    protected $fillable = [
        'buzzer_session_id',
        'position',
        'question_text',
        'opened_at',
    ];

    protected $casts = [
        'position' => 'integer',
        'opened_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(BuzzerSession::class, 'buzzer_session_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(BuzzerAttempt::class);
    }

    public function currentHolder(): ?BuzzerAttempt
    {
        return $this->attempts()
            ->with('user:id,name,prenom')
            ->whereNull('result')
            ->orderBy('reaction_ms')
            ->first();
    }
}
