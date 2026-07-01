<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainerPathActivityAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'module_key',
        'chapter_key',
        'lesson_key',
        'activity_key',
        'activity_type',
        'attempt_number',
        'total_items',
        'correct_items',
        'is_success',
        'submitted_answer',
        'expected_answer',
        'wrong_items',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'total_items' => 'integer',
            'correct_items' => 'integer',
            'is_success' => 'boolean',
            'submitted_answer' => 'array',
            'expected_answer' => 'array',
            'wrong_items' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
