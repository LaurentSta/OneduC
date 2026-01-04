<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SkillReferential extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];

    public function domains()
    {
        return $this->hasMany(SkillDomain::class, 'skill_referential_id');
    }

    public function skills()
    {
        return $this->hasMany(Skill::class, 'skill_referential_id');
    }
}
