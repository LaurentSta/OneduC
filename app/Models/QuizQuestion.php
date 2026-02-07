<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'position'   => 'integer',
        'lecture_id' => 'integer',
        'points'     => 'integer',
    ];

    /**
     * Relation pour récupérer les choix possibles (A, B, C...).
     * Nommée 'answers' pour fonctionner avec ton FormateurController.
     */
    public function answers()
    {
        return $this->hasMany(QuizOption::class, 'question_id')->orderBy('position')->orderBy('id');
    }

    /**
     * Alias 'options' si tu préfères utiliser ce terme plus tard.
     */
    public function options()
    {
        return $this->answers();
    }

    /**
     * La question appartient à une leçon (la banque est liée à la leçon).
     */
    public function lecture()
    {
        return $this->belongsTo(ModuleLecture::class, 'lecture_id');
    }
}