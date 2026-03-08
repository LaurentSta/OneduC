<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ScormPackage;
use App\Models\ScormPackageVersion;
use Illuminate\Support\Str;

class ModuleLecture extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'duration' => 'integer',
        'quiz_enabled' => 'boolean',
        'use_active_scorm_version' => 'boolean',
        'slides_converted_at' => 'datetime',
    ];

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

    public function quizQuestions()
    {
        return $this->hasMany(\App\Models\QuizQuestion::class, 'lecture_id');
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

    public function resolveScormVersion(): ?ScormPackageVersion
    {
        if ($this->use_active_scorm_version && $this->scormPackage?->activeVersion) {
            return $this->scormPackage->activeVersion;
        }

        return $this->scormPackageVersion;
    }

    public function getScormIndexPathAttribute(): ?string
    {
        $version = $this->resolveScormVersion();

        return $version?->index_path ?? $this->scorm_path;
    }

    public function getScormCacheTokenAttribute(): ?string
    {
        $version = $this->resolveScormVersion();

        if ($version?->imported_at) {
            return (string) $version->imported_at->timestamp;
        }

        if ($version?->updated_at) {
            return (string) $version->updated_at->timestamp;
        }

        return $this->updated_at ? (string) $this->updated_at->timestamp : null;
    }

    public function getScormAssetUrlAttribute(): ?string
    {
        $path = trim((string) ($this->scorm_index_path ?? ''));
        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            $url = $path;
        } elseif (Str::startsWith($path, '/')) {
            $url = url($path);
        } else {
            $url = asset($path);
        }

        $token = $this->scorm_cache_token;
        if (!$token) {
            return $url;
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . 'v=' . rawurlencode($token);
    }

    public function objectives()
    {
        return $this->hasMany(LectureObjective::class, 'lecture_id')
                    ->orderBy('position');
    }

    // J'AI SUPPRIMÉ LES FONCTIONS DE CALCUL ICI CAR ELLES N'ONT RIEN A FAIRE DANS LA LEÇON
}
