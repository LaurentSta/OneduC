<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;

    private const DEFAULT_ESTIMATED_SECONDS_PER_QUESTION = 30;

    protected $fillable = [
        'category_id', 'subcategory_id', 'formateur_id', 'is_trainer_authored',
        'module_image', 'header_image',
        'module_title', 'module_name', 'module_name_slug', 'description', 'objectifs',
        'module_video', 'label', 'duree', 'resources', 'certificat', 'prerequi',
        'bestseller', 'vedette', 'surevalue', 'status', 'evaluation_id',
        'estimated_question_seconds',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(\App\Models\SubCategory::class, 'subcategory_id');
    }

    public function stagiaires()
    {
        return User::where('role', 'stagiaire')
            ->whereHas('groupesStagiaire', function ($q) {
                $q->whereIn('group_id', $this->groups()->pluck('groups.id'));
            });
    }

    public function formateur()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function sections()
    {
        return $this->hasMany(ModuleSection::class)->orderBy('position');
    }

    public function lectures()
    {
        return $this->hasMany(\App\Models\ModuleLecture::class, 'module_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_module');
    }

    public function moduleResources()
    {
        return $this->hasMany(LessonResource::class, 'module_id')
            ->orderBy('position')
            ->orderByDesc('id');
    }

    public function evaluation()
    {
        return $this->belongsTo(\App\Models\Evaluation::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 1);
    }

    public function scopeAuthoredByTrainer($q, int $trainerId)
    {
        return $q->where('formateur_id', $trainerId)->where('is_trainer_authored', true);
    }

    public function scopePubliclyListable($q)
    {
        return $q->where('status', 1)->where('is_trainer_authored', false);
    }

    protected $casts = [
        'status' => 'boolean',
        'is_trainer_authored' => 'boolean',
        'objectifs' => 'array',
        'estimated_question_seconds' => 'integer',
    ];

    public function isVisibleTo(?\App\Models\User $user): bool
    {
        if ($user && $user->role === 'admin') {
            return true;
        }

        return (bool) $this->status;
    }

    // --- C'EST ICI QU'ON AJOUTE LE CALCUL DU TEMPS ---

    public function getEstimatedQuestionCountAttribute(): int
    {
        return $this->moduleLecturesForDuration()->sum(function ($lecture) {
            $plannedScorm = (int) ($lecture->question_count ?? 0);
            $plannedQuiz = (bool) ($lecture->quiz_enabled ?? false)
                ? (int) ($lecture->quiz_questions_per_attempt ?? 0)
                : 0;

            return max($plannedScorm, $plannedQuiz);
        });
    }

    public function getEstimatedQuestionSecondsAttribute(): int
    {
        return $this->estimated_question_count * $this->estimatedSecondsPerQuestion();
    }

    public function getTotalSecondsAttribute(): int
    {
        $lessonSeconds = $this->moduleLecturesForDuration()->sum(function ($lecture) {
            return max(0, (int) ($lecture->duration ?? 0)) * 60;
        });

        return (int) $lessonSeconds + $this->estimated_question_seconds;
    }

    /**
     * Calcule la durée totale en minutes (leçons + questions estimées).
     * Accessible via : $module->total_minutes
     */
    public function getTotalMinutesAttribute()
    {
        return (int) ceil($this->total_seconds / 60);
    }

    /**
     * Affiche la durée formatée (ex: "2h 15min" ou "45 min").
     * Accessible via : $module->formatted_duration
     */
    public function getFormattedDurationAttribute()
    {
        return $this->formatDurationFromSeconds($this->total_seconds);
    }

    public function getEstimatedSecondsForUser(?int $userId): int
    {
        if (! $userId) {
            return $this->total_seconds;
        }

        $lectures = $this->moduleLecturesForDuration();
        $lectureIds = $lectures->pluck('id')->filter()->values();

        if ($lectureIds->isEmpty()) {
            return $this->total_seconds;
        }

        $quizAttemptsByLecture = QuizAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('lecture_id', $lectureIds)
            ->selectRaw('lecture_id, COUNT(*) as total')
            ->groupBy('lecture_id')
            ->pluck('total', 'lecture_id');

        $scormAttemptsByLecture = ScormScore::query()
            ->where('user_id', $userId)
            ->whereIn('lecture_id', $lectureIds)
            ->selectRaw('lecture_id, MAX(COALESCE(attempts_count, 1)) as total')
            ->groupBy('lecture_id')
            ->pluck('total', 'lecture_id');

        $lessonSeconds = $lectures->sum(function ($lecture) {
            return max(0, (int) ($lecture->duration ?? 0)) * 60;
        });

        $questionSeconds = $lectures->sum(function ($lecture) use ($quizAttemptsByLecture, $scormAttemptsByLecture) {
            $plannedScorm = (int) ($lecture->question_count ?? 0);
            $plannedQuiz = (bool) ($lecture->quiz_enabled ?? false)
                ? (int) ($lecture->quiz_questions_per_attempt ?? 0)
                : 0;
            $plannedQuestions = max($plannedScorm, $plannedQuiz);

            if ($plannedQuestions <= 0) {
                return 0;
            }

            $attempts = max(
                1,
                (int) ($quizAttemptsByLecture[$lecture->id] ?? 0),
                (int) ($scormAttemptsByLecture[$lecture->id] ?? 0),
            );

            return $plannedQuestions * $attempts * $this->estimatedSecondsPerQuestion();
        });

        return (int) $lessonSeconds + (int) $questionSeconds;
    }

    public function getFormattedDurationForUser(?int $userId): ?string
    {
        return $this->formatDurationFromSeconds($this->getEstimatedSecondsForUser($userId));
    }

    private function moduleLecturesForDuration()
    {
        if ($this->hasLoadedLecturesForDuration()) {
            return $this->sections->flatMap->lectures;
        }

        return ModuleLecture::query()
            ->where('module_id', $this->id)
            ->get([
                'id',
                'module_id',
                'duration',
                'question_count',
                'quiz_enabled',
                'quiz_questions_per_attempt',
            ]);
    }

    private function hasLoadedLecturesForDuration(): bool
    {
        if (! $this->relationLoaded('sections')) {
            return false;
        }

        $requiredAttributes = ['id', 'duration', 'question_count', 'quiz_enabled', 'quiz_questions_per_attempt'];

        return $this->sections->every(function ($section) use ($requiredAttributes) {
            if (! $section->relationLoaded('lectures')) {
                return false;
            }

            return $section->lectures->every(function ($lecture) use ($requiredAttributes) {
                $attributes = $lecture->getAttributes();

                foreach ($requiredAttributes as $attribute) {
                    if (! array_key_exists($attribute, $attributes)) {
                        return false;
                    }
                }

                return true;
            });
        });
    }

    private function formatDurationFromSeconds(int $seconds): ?string
    {
        if ($seconds <= 0) {
            return null;
        }

        $hours = intdiv($seconds, 3600);
        $remainingSeconds = $seconds % 3600;
        $minutes = intdiv($remainingSeconds, 60);
        $remainingSeconds = $remainingSeconds % 60;

        $parts = [];

        if ($hours > 0) {
            $parts[] = "{$hours}h";
        }

        if ($minutes > 0) {
            $parts[] = "{$minutes}min";
        }

        if ($remainingSeconds > 0) {
            $parts[] = "{$remainingSeconds}s";
        }

        return implode(' ', $parts);
    }

    private function estimatedSecondsPerQuestion(): int
    {
        return max(1, (int) ($this->attributes['estimated_question_seconds'] ?? self::DEFAULT_ESTIMATED_SECONDS_PER_QUESTION));
    }
}
