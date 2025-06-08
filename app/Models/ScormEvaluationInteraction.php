<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScormEvaluationInteraction extends Model
{
    protected $fillable = [
        'user_id',
        'evaluation_id',
        'interaction_id',
        'interaction_type',
        'interaction_weighting',
        'result',
        'response',
        'correct_response',
        'latency',
        'time',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }
}
