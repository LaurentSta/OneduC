<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoSegmentTracking extends Model
{
    protected $fillable = [
        'user_id',
        'video_id',
        'segment_start',
        'segment_end',
        'watch_count',
        'total_watch_time'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
