<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScaleSessionResponse extends Model
{
    protected $fillable = [
        'scale_session_id',
        'user_id',
        'question_index',
        'value',
    ];

    protected $casts = [
        'value' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ScaleSession::class, 'scale_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
