<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Evaluation;


class ScormScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lecture_id',
        'first_score',
        'best_score',
        'is_completed',
        'attempts_count',
        'last_score',
        'last_attempt_at',
        'questions_answered',
        'interaction_id',
        'interaction_type',
        'interaction_weighting',
        'lesson_status',
    ];


    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function lecture()
    {
        return $this->belongsTo(\App\Models\ModuleLecture::class);
    }
   

}
