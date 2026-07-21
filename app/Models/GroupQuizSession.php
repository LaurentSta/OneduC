<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupQuizSession extends Model
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_QUESTION_OPEN = 'question_open';
    public const STATUS_ANSWER_REVEALED = 'answer_revealed';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'formateur_id',
        'group_id',
        'access_code',
        'status',
        'current_position',
        'total_questions',
        'answer_revealed_at',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'current_position' => 'integer',
        'total_questions' => 'integer',
        'answer_revealed_at' => 'datetime',
        'started_at' => 'datetime',
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

    public function sessionQuestions(): HasMany
    {
        return $this->hasMany(GroupQuizSessionQuestion::class)->orderBy('position');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(GroupQuizSessionParticipant::class);
    }

    public function isForAllGroups(): bool
    {
        return $this->group_id === null;
    }

    public function currentSessionQuestion(): ?GroupQuizSessionQuestion
    {
        $position = (int) $this->current_position;
        if ($position <= 0) {
            return null;
        }

        if ($this->relationLoaded('sessionQuestions')) {
            return $this->sessionQuestions->firstWhere('position', $position);
        }

        return $this->sessionQuestions()->where('position', $position)->first();
    }

    public function isWaiting(): bool
    {
        return (string) $this->status === self::STATUS_WAITING;
    }

    public function isQuestionOpen(): bool
    {
        return (string) $this->status === self::STATUS_QUESTION_OPEN;
    }

    public function isAnswerRevealed(): bool
    {
        return (string) $this->status === self::STATUS_ANSWER_REVEALED;
    }

    public function isClosed(): bool
    {
        return (string) $this->status === self::STATUS_CLOSED;
    }
}
