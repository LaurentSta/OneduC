<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollQuestionnaire extends Model
{
    protected $fillable = [
        'formateur_id',
        'title',
        'questions',
    ];

    protected $casts = [
        'questions' => 'array',
    ];

    public function formateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(PollSession::class, 'poll_questionnaire_id');
    }
}
