<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestionnaire extends Model
{
    protected $fillable = [
        'formateur_id',
        'title',
    ];

    public function formateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'questionnaire_id')->orderBy('id');
    }
}
