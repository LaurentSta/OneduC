<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Evaluation;

class ScormResult extends Model
{
    protected $fillable = [
        'user_id',
        'lecture_id',
        'scorm_key',
        'scorm_value',
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
