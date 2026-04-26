<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilotNotificationPreference extends Model
{
    use HasFactory;

    public const FREQUENCIES = [
        'immediate' => 'Immediate',
        'daily' => 'Quotidienne',
        'weekly' => 'Hebdomadaire',
    ];

    protected $fillable = [
        'user_id',
        'in_app_enabled',
        'email_enabled',
        'frequency',
        'event_types',
    ];

    protected function casts(): array
    {
        return [
            'in_app_enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'event_types' => 'array',
        ];
    }

    public static function defaultEventTypes(): array
    {
        return array_keys(PilotTask::EVENT_TYPES);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

