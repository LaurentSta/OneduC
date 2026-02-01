<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'label',
        'description',
        'image_path',
        'is_active',
    ];

    public function competencies()
    {
        return $this->belongsToMany(
            Competency::class,
            'badge_competency',
            'badge_id',
            'competency_id'
        )
        ->withPivot(['position'])
        ->withTimestamps()
        ->orderByPivot('position');
    }
}
