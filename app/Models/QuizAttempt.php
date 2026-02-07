<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $guarded = []; // Plus simple que fillable pour le dev

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
        'passed'      => 'boolean',
        'score'       => 'integer',
        'percent'     => 'integer',
    ];

    /**
     * Les réponses données spécifiquement lors de CETTE tentative.
     */
    public function attemptQuestions()
    {
        return $this->hasMany(QuizAttemptQuestion::class, 'attempt_id');
    }

    public function lecture()
    {
        return $this->belongsTo(ModuleLecture::class, 'lecture_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}