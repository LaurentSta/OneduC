<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $guarded = [];

    public function lecture() { return $this->belongsTo(ModuleLecture::class, 'lecture_id'); }
    public function options() { return $this->hasMany(QuizOption::class, 'question_id')->orderBy('position'); }

    public function correctOptionIds(): array
    {
        return $this->options()->where('is_correct', true)->pluck('id')->map(fn($v)=>(int)$v)->all();
    }
}
