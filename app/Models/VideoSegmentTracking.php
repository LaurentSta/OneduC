<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoSegmentTracking extends Model
{
    protected $fillable = [
        'user_id',
        'lecture_id',
        'segment_start',
        'segment_end',
        'watch_count',
        'total_watch_time'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(ModuleLecture::class, 'lecture_id');
    }
}
