<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrueFalseSession extends Model
{
    protected $fillable = [
        'formateur_id',
        'group_id',
        'title',
        'questions',
        'access_code',
        'is_active',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'questions' => 'array',
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

    public function responses(): HasMany
    {
        return $this->hasMany(TrueFalseSessionResponse::class);
    }
}
