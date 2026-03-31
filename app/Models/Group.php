<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'start_date',
        'end_date',
        'temporary_password',
        'instructor_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'temporary_password' => 'encrypted',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id')
                    ->wherePivot('role_in_group', 'stagiaire');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'group_module')
        ->withPivot('position')
        ->orderBy('group_module.position');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id');
    }

    public function observers()
    {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id')
            ->wherePivot('role_in_group', 'observateur');
    }

    public function whiteboard()
    {
        return $this->hasOne(GroupWhiteboard::class);
    }

    public function wordClouds()
    {
        return $this->hasMany(WordCloud::class);
    }

    public function liveQuizSessions()
    {
        return $this->hasMany(LiveQuizSession::class);
    }
}
