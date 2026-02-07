<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttemptQuestion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'given_answer' => 'array', // Pour stocker [1, 2] si choix multiple, ou "texte"
        'answered_at'  => 'datetime',
        'is_correct'   => 'boolean',
        'position'     => 'integer',
    ];

    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    /**
     * Lien vers la question d'origine dans la banque.
     */
    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}