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
    public static function getStatsForUser(int $userId): array
    {
        return [
            'totalVideoWatchTime' => self::where('user_id', $userId)->sum('total_watch_time'),
            'totalVideoSegments' => self::where('user_id', $userId)->count(),
            'totalVideoReplays' => self::where('user_id', $userId)
                ->where('watch_count', '>', 1)
                ->count(),
        ];
    }

}
