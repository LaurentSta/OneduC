<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningObjective extends Model
{
    protected $fillable = [
        'module_id',
        'user_id',
        'progress',
        'started_at',
        'completed_at',
    ];
}
