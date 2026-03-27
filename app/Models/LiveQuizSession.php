<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveQuizSession extends Model
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_QUESTION_OPEN = 'question_open';
    public const STATUS_ANSWER_REVEALED = 'answer_revealed';
    public const STATUS_CLOSED = 'closed';

    protected $guarded = [];

    protected $casts = [
        'answer_revealed_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'current_position' => 'integer',
        'total_questions' => 'integer',
    ];

    public function formateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ModuleSection::class, 'section_id');
    }

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(ModuleLecture::class, 'lecture_id');
    }

    public function sessionQuestions(): HasMany
    {
        return $this->hasMany(LiveQuizSessionQuestion::class)->orderBy('position');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(LiveQuizSessionParticipant::class);
    }

    public function currentSessionQuestion(): ?LiveQuizSessionQuestion
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
