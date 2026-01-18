<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ScormPackage;
use App\Models\ScormPackageVersion;

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

    // app/Models/ModuleLecture.php
    public function quizQuestions()
{
    return $this->hasMany(
        \App\Models\QuizQuestion::class,
        'lecture_id'
    );
}


    public function quizAttempts()
    {
        return $this->hasMany(\App\Models\QuizAttempt::class, 'lecture_id');
    }
    public function scormPackage(): BelongsTo
    {
        return $this->belongsTo(ScormPackage::class, 'scorm_package_id');
    }

    public function scormPackageVersion(): BelongsTo
    {
        return $this->belongsTo(ScormPackageVersion::class, 'scorm_package_version_id');
    }
    public function getScormIndexPathAttribute(): ?string
    {
        if ($this->use_active_scorm_version && $this->scormPackage?->activeVersion) {
            return $this->scormPackage->activeVersion->index_path;
        }

        return $this->scormPackageVersion?->index_path;
    }

}
