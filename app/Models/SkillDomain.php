<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SkillDomain extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'skill_referential_id',
        'name',
        'description',
        'position',
        'status',
    ];

    public function referential()
    {
        return $this->belongsTo(SkillReferential::class, 'skill_referential_id');
    }

    public function skills()
    {
        return $this->hasMany(Skill::class, 'skill_domain_id');
    }
}
