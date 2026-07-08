<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComponentFinderSession extends Model
{
    protected $fillable = [
        'formateur_id',
        'group_id',
        'title',
        'image_path',
        'zones',
        'access_code',
        'is_active',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'zones' => 'array',
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

    public function attempts(): HasMany
    {
        return $this->hasMany(ComponentFinderAttempt::class);
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/'.$this->image_path);
    }
}
