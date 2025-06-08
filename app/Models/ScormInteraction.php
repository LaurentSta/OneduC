<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Evaluation;


class ScormInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lecture_id',
        'interaction_id',
        'interaction_type',
        'interaction_weighting',
        'result',
        'response',
        'correct_response',
        'latency',
        'time',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lecture()
    {
        return $this->belongsTo(ModuleLecture::class);
    }


}
