<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleLecture extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function section()
    {
        return $this->belongsTo(ModuleSection::class, 'section_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
    public function progressions()
    {
        return $this->hasMany(Progression::class, 'lecture_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(LessonFeedback::class, 'lesson_id');
    }



}
