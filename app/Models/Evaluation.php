<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $fillable = [
        'titre',
        'scorm_path',
    ];

    // 🔗 Relation : une évaluation peut être liée à plusieurs modules
    public function modules()
    {
        return $this->hasMany(\App\Models\Module::class, 'evaluation_id');
    }

    public function scormResults()
    {
        return $this->hasMany(ScormEvaluationResult::class);
    }

    public function scormScores()
    {
        return $this->hasMany(ScormEvaluationScore::class);
    }

    public function scormInteractions()
    {
        return $this->hasMany(ScormEvaluationInteraction::class);
    }

}
