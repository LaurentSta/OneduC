<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Competency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'label',
        'description',
        'is_active',
    ];

    public function objectives()
    {
        return $this->belongsToMany(
            LectureObjective::class,
            'lecture_objective_competency',
            'competency_id',
            'lecture_objective_id'
        )
        ->withPivot(['position'])
        ->withTimestamps()
        ->orderByPivot('position');
    }

    public function badges()
    {
        return $this->belongsToMany(
            Badge::class,
            'badge_competency',
            'competency_id',
            'badge_id'
        )
        ->withPivot(['position'])
        ->withTimestamps()
        ->orderByPivot('position');
    }
}
