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
        'temporary_password',
        'instructor_id',
    ];

    protected $casts = [
        'temporary_password' => 'encrypted',
    ];

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


}
