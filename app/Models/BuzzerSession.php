<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuzzerSession extends Model
{
    public const STATUS_WAITING = 'waiting';

    public const STATUS_QUESTION_OPEN = 'question_open';

    public const STATUS_REVEALED = 'revealed';

    public const STATUS_CLOSED = 'closed';

    public const MODE_PRESENTIEL = 'presentiel';

    public const MODE_DISTANCE = 'distance';

    protected $fillable = [
        'formateur_id',
        'group_id',
        'title',
        'mode',
        'access_code',
        'status',
        'current_position',
        'total_questions',
        'opened_at',
        'ended_at',
    ];

    protected $casts = [
        'current_position' => 'integer',
        'total_questions' => 'integer',
        'opened_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function formateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(BuzzerQuestion::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(BuzzerParticipant::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(BuzzerAttempt::class);
    }

    public function currentQuestion(): ?BuzzerQuestion
    {
        if ($this->current_position < 1) {
            return null;
        }

        if ($this->relationLoaded('questions')) {
            return $this->questions->firstWhere('position', $this->current_position);
        }

        return $this->questions()
            ->where('position', $this->current_position)
            ->first();
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }
}
