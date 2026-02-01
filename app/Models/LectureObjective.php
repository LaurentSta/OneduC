<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LectureObjective extends Model
{
    use HasFactory;

    protected $fillable = [
        'lecture_id',
        'title',
        'description',
        'position',
    ];

    /**
     * Un objectif appartient à une leçon
     */
    public function lecture()
    {
        return $this->belongsTo(ModuleLecture::class, 'lecture_id');
    }
    public function competencies()
    {
        return $this->belongsToMany(
            Competency::class,
            'lecture_objective_competency',
            'lecture_objective_id',
            'competency_id'
        )->withPivot(['position'])->withTimestamps()->orderBy('pivot_position');
    }

}
