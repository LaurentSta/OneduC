<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WordCloud extends Model
{
    protected $fillable = [
        'module_id',
        'group_id',
        'title',
        'question',
        'questions',
        'current_question_index',
        'access_code',
        'is_active',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'questions' => 'array',
        'current_question_index' => 'integer',
        'is_active' => 'boolean',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function getQuestionsArrayAttribute(): array
    {
        if (!empty($this->questions)) {
            return array_values(array_filter(
                $this->questions,
                fn ($question) => is_string($question) && trim($question) !== ''
            ));
        }
        return $this->question ? [$this->question] : [];
    }

    public function getActiveQuestionIndexAttribute(): int
    {
        $maxIndex = max(0, count($this->questions_array) - 1);

        return min(max((int) ($this->current_question_index ?? 0), 0), $maxIndex);
    }

    public function getActiveQuestionAttribute(): ?string
    {
        return $this->questions_array[$this->active_question_index] ?? null;
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(WordCloudEntry::class);
    }
}
