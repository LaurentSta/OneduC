<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizOption extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_correct'  => 'boolean',
        'question_id' => 'integer',
        'position'    => 'integer',
    ];

    /**
     * L'option appartient à une question de la banque.
     */
    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}