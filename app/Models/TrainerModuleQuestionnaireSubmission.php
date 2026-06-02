<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainerModuleQuestionnaireSubmission extends Model
{
    protected $fillable = [
        'submission_uuid',
        'user_id',
        'module_number',
        'module_key',
        'questionnaire_key',
        'questionnaire_version',
        'responses',
        'submitted_at',
        'emailed_at',
    ];

    protected function casts(): array
    {
        return [
            'responses' => 'array',
            'submitted_at' => 'datetime',
            'emailed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
