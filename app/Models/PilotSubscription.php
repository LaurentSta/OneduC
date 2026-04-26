<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilotSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'task_id',
        'notify_in_app',
        'notify_mail',
        'frequency',
    ];

    protected function casts(): array
    {
        return [
            'notify_in_app' => 'boolean',
            'notify_mail' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project()
    {
        return $this->belongsTo(PilotProject::class, 'project_id');
    }

    public function task()
    {
        return $this->belongsTo(PilotTask::class, 'task_id');
    }
}

