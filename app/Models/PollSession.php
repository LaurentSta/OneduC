<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollSession extends Model
{
    protected $fillable = [
        'formateur_id',
        'group_id',
        'poll_questionnaire_id',
        'access_code',
        'is_active',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function formateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(PollQuestionnaire::class, 'poll_questionnaire_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(PollSessionResponse::class);
    }

    public function getTitleAttribute(): string
    {
        return (string) ($this->questionnaire?->title ?? '');
    }

    public function getQuestionsAttribute(): array
    {
        return $this->questionnaire?->questions ?? [];
    }
}
