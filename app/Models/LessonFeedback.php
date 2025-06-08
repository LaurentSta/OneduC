<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LessonFeedback extends Model
{
    protected $table = 'lesson_feedbacks';
    use SoftDeletes; // 👈 ajoute cette ligne
    protected $fillable = [
        'user_id',
        'lesson_id',
        'comment',
        'type',
        'rating',
        'urgency', // ✅ Ajout ici
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(ModuleLecture::class, 'lesson_id');
    }
}
