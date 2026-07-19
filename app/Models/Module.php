<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Module extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    public const PUBLICATION_DRAFT = 'draft';

    public const PUBLICATION_PUBLISHED = 'published';

    public const PUBLICATION_ARCHIVED = 'archived';

    protected static function booted(): void
    {
        static::creating(function (Module $module): void {
            if ((bool) $module->is_trainer_authored) {
                return;
            }

            $module->catalogue_key ??= (string) Str::uuid();
            $module->version_number ??= 1;
            $module->publication_state ??= (bool) $module->status
                ? self::PUBLICATION_PUBLISHED
                : self::PUBLICATION_DRAFT;

            if ($module->publication_state === self::PUBLICATION_PUBLISHED) {
                $module->published_at ??= now();
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('lesson-images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

        $this->addMediaCollection('lesson-videos')
            ->acceptsMimeTypes(['video/mp4', 'video/webm', 'video/ogg']);

        $this->addMediaCollection('lesson-audios')
            ->acceptsMimeTypes(['audio/mpeg', 'audio/wav', 'audio/x-wav', 'audio/ogg', 'audio/mp4', 'audio/x-m4a']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('display')
            ->fit(Fit::Max, 1600, 1600)
            ->nonQueued();
    }

    private const DEFAULT_ESTIMATED_SECONDS_PER_QUESTION = 30;

    protected $fillable = [
        'category_id', 'subcategory_id', 'formateur_id', 'created_by', 'is_trainer_authored',
        'catalogue_key', 'version_number', 'publication_state', 'published_at', 'source_module_id',
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

    public function referent()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function moduleSource()
    {
        return $this->belongsTo(self::class, 'source_module_id')->withTrashed();
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

    public function scopeCatalogue(Builder $query): Builder
    {
        return $query->where('is_trainer_authored', false);
    }

    public function scopePublishedCatalogue(Builder $query): Builder
    {
        return $query
            ->catalogue()
            ->where('publication_state', self::PUBLICATION_PUBLISHED);
    }

    public function scopePubliclyListable(Builder $query): Builder
    {
        return $query
            ->publishedCatalogue()
            ->where('status', true);
    }

    public function scopeAssignable(Builder $query): Builder
    {
        return $query
            ->where('status', true)
            ->where(function (Builder $visibilite): void {
                $visibilite
                    ->where('is_trainer_authored', true)
                    ->orWhere(function (Builder $catalogue): void {
                        $catalogue
                            ->where('is_trainer_authored', false)
                            ->where('publication_state', self::PUBLICATION_PUBLISHED);
                    });
            });
    }

    public function scopeAssignableAuFormateur(Builder $query, int $formateurId): Builder
    {
        return $query
            ->assignable()
            ->where(function (Builder $visibilite) use ($formateurId): void {
                $visibilite
                    ->where(function (Builder $catalogue): void {
                        $catalogue
                            ->where('is_trainer_authored', false)
                            ->where('publication_state', self::PUBLICATION_PUBLISHED);
                    })
                    ->orWhere(function (Builder $creationFormateur) use ($formateurId): void {
                        $creationFormateur
                            ->where('is_trainer_authored', true)
                            ->where('formateur_id', $formateurId);
                    });
            });
    }

    public function scopeVisibleAuFormateur(Builder $query, int $formateurId): Builder
    {
        return $query->where(function (Builder $visibilite) use ($formateurId): void {
            $visibilite
                ->where(function (Builder $cataloguePublie): void {
                    $cataloguePublie->publishedCatalogue()->where('status', true);
                })
                ->orWhere(function (Builder $creationPersonnelle) use ($formateurId): void {
                    $creationPersonnelle
                        ->authoredByTrainer($formateurId)
                        ->where('status', true);
                })
                ->orWhere(function (Builder $viaGroupe) use ($formateurId): void {
                    $viaGroupe
                        ->whereHas('groups', fn (Builder $groupes) => $groupes->accessibleByTrainer($formateurId))
                        ->where(function (Builder $contenu): void {
                            $contenu
                                ->where(function (Builder $archiveCatalogue): void {
                                    $archiveCatalogue
                                        ->where('is_trainer_authored', false)
                                        ->where('publication_state', self::PUBLICATION_ARCHIVED);
                                })
                                ->orWhere(function (Builder $cataloguePublie): void {
                                    $cataloguePublie
                                        ->where('is_trainer_authored', false)
                                        ->where('publication_state', self::PUBLICATION_PUBLISHED)
                                        ->where('status', true);
                                })
                                ->orWhere(function (Builder $creationPersonnelle): void {
                                    $creationPersonnelle
                                        ->where('is_trainer_authored', true)
                                        ->where('status', true);
                                });
                        });
                });
        });
    }

    public function scopeVersionsOf(Builder $query, Module|string $formation): Builder
    {
        $cleCatalogue = $formation instanceof self
            ? $formation->catalogue_key
            : $formation;

        return $query
            ->catalogue()
            ->where('catalogue_key', $cleCatalogue)
            ->orderByDesc('version_number');
    }

    protected $casts = [
        'status' => 'boolean',
        'is_trainer_authored' => 'boolean',
        'objectifs' => 'array',
        'estimated_question_seconds' => 'integer',
        'version_number' => 'integer',
        'published_at' => 'datetime',
    ];

    public function isVisibleTo(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'formateur') {
            if ($this->estArchivee()) {
                return $this->groups()->accessibleByTrainer((int) $user->id)->exists();
            }

            if (! $this->status) {
                return false;
            }

            // Une version publiée du catalogue reste parcourable par tout formateur.
            if ($this->estFormationCatalogue()) {
                return $this->estPubliee();
            }

            if ((int) $this->formateur_id === (int) $user->id) {
                return true;
            }

            return $this->groups()->accessibleByTrainer((int) $user->id)->exists();
        }

        if (! $this->status) {
            return false;
        }

        if ($user->role === 'stagiaire') {
            return $user->aAccesAuModule($this->id);
        }

        return false;
    }

    public function estFormationCatalogue(): bool
    {
        return ! (bool) $this->is_trainer_authored;
    }

    public function estBrouillonCatalogue(): bool
    {
        return $this->estFormationCatalogue()
            && $this->publication_state === self::PUBLICATION_DRAFT;
    }

    public function estPubliee(): bool
    {
        return $this->estFormationCatalogue()
            && $this->publication_state === self::PUBLICATION_PUBLISHED;
    }

    public function estArchivee(): bool
    {
        return $this->estFormationCatalogue()
            && $this->publication_state === self::PUBLICATION_ARCHIVED;
    }

    public function estModifiableParAdministrateur(): bool
    {
        return $this->estBrouillonCatalogue();
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
