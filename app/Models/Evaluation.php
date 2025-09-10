<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Module;
use App\Models\ScormEvaluationResult;
use App\Models\ScormEvaluationScore;
use App\Models\ScormEvaluationInteraction;

class Evaluation extends Model
{
    protected $fillable = [
        'titre',
        'scorm_path',
    ];

    /**
     * Une évaluation peut être liée à plusieurs modules.
     */
    public function modules()
    {
        // FK sur modules.evaluation_id
        return $this->hasMany(Module::class, 'evaluation_id', 'id');
        // ->withDefault() inutile ici côté hasMany
    }

    /**
     * Traces K/V SCORM de l'évaluation.
     */
    public function scormResults()
    {
        // FK sur scorm_evaluation_results.evaluation_id
        return $this->hasMany(ScormEvaluationResult::class, 'evaluation_id', 'id');
    }

    /**
     * Scores agrégés par user+évaluation.
     */
    public function scormScores()
    {
        // FK sur scorm_evaluation_scores.evaluation_id
        return $this->hasMany(ScormEvaluationScore::class, 'evaluation_id', 'id');
    }

    /**
     * Historique des interactions.
     */
    public function scormInteractions()
    {
        // FK sur scorm_evaluation_interactions.evaluation_id
        return $this->hasMany(ScormEvaluationInteraction::class, 'evaluation_id', 'id');
    }
}
