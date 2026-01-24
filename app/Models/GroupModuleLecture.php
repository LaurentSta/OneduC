<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupModuleLecture extends Model
{
    protected $fillable = [
        'group_id',
        'module_id',
        'lecture_id',
        'position',
        'is_enabled',
    ];
}
