<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComponentFinderAttempt extends Model
{
    protected $fillable = [
        'component_finder_session_id',
        'user_id',
        'score',
        'total',
        'duration_seconds',
        'details',
    ];

    protected $casts = [
        'score' => 'integer',
        'total' => 'integer',
        'duration_seconds' => 'integer',
        'details' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ComponentFinderSession::class, 'component_finder_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
