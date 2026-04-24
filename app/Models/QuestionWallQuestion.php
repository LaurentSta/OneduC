<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionWallQuestion extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_TREATED = 'treated';
    public const STATUS_ACTIVITY = 'activity';
    public const STATUS_LATER = 'later';

    protected $fillable = [
        'question_wall_id',
        'user_id',
        'body',
        'is_anonymous',
        'status',
        'acted_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'acted_at' => 'datetime',
    ];

    public function wall(): BelongsTo
    {
        return $this->belongsTo(QuestionWall::class, 'question_wall_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(QuestionWallVote::class, 'question_wall_question_id');
    }

    public function statusLabel(): string
    {
        return match ((string) $this->status) {
            self::STATUS_TREATED => 'Traitee',
            self::STATUS_ACTIVITY => 'A transformer en activite',
            self::STATUS_LATER => 'A garder pour plus tard',
            default => 'Ouverte',
        };
    }
}
