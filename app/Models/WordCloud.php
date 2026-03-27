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
