<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordCloudEntry extends Model
{
    protected $fillable = [
        'word_cloud_id',
        'user_id',
        'answer',
        'normalized_answer',
    ];

    public function wordCloud(): BelongsTo
    {
        return $this->belongsTo(WordCloud::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
