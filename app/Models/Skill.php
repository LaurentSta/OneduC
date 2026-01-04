<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skill extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'skill_referential_id',
        'skill_domain_id',
        'name',
        'code',
        'description',
        'position',
        'status',
    ];

    public function referential()
    {
        return $this->belongsTo(SkillReferential::class, 'skill_referential_id');
    }

    public function domain()
    {
        return $this->belongsTo(SkillDomain::class, 'skill_domain_id');
    }
}
