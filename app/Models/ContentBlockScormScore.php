<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentBlockScormScore extends Model
{
    protected $fillable = [
        'user_id',
        'lecture_id',
        'content_block_key',
        'lesson_status',
        'first_score',
        'best_score',
        'last_score',
        'attempts_count',
        'is_completed',
        'session_time',
        'last_session_time',
        'last_attempt_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'last_attempt_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lecture()
    {
        return $this->belongsTo(ModuleLecture::class);
    }
}
