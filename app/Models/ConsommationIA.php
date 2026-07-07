<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsommationIA extends Model
{
    protected $table = 'consommations_ia';

    protected $fillable = [
        'formateur_id',
        'type',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
    ];

    public function formateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }
}
