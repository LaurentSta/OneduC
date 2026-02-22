<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilotSubscription extends Model
{
    use HasFactory;

    protected $guarded = [];

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

