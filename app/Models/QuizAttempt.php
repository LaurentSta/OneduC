<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Builder;

class QuizAttempt extends Model
{
    use Prunable;

    protected $guarded = [];
    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'passed' => 'boolean',
    ];

    public function lecture() { return $this->belongsTo(ModuleLecture::class, 'lecture_id'); }
    public function user() { return $this->belongsTo(User::class); }
    public function questions() { return $this->hasMany(QuizAttemptQuestion::class, 'attempt_id')->orderBy('position'); }

    // Conservation 24 mois :contentReference[oaicite:8]{index=8}
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subMonths(24));
    }
}
