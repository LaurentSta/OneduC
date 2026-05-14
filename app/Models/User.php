<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if ($user->role !== 'formateur' || filled($user->adhesion_status)) {
                return;
            }

            $user->adhesion_status = 'active';
            $user->adhesion_valid_until = now()->addYear()->toDateString();
            $user->adhesion_verified_at = now();
        });

        static::deleting(function (User $user): void {
            if ($user->role === 'stagiaire') {
                $user->cleanupRelatedStagiaireData();
                return;
            }

            if ($user->role === 'formateur') {
                $user->cleanupOwnedGroupsAndLinkedStagiaires();
            }
        });
    }

    protected $fillable = [
        'prenom',
        'name',
        'username',
        'email',
        'password',
        'photo',
        'phone',
        'address',
        'societe',
        'role',
        'status',
        'adhesion_status',
        'adhesion_valid_until',
        'adhesion_verified_at',
        'adhesion_verified_by',
        'formateur_id',
        'code_acces',
        'total_site_time',
        'password_changed_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    // ✅ Casts de champs
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
            'adhesion_valid_until' => 'date',
            'adhesion_verified_at' => 'datetime',
            'password_changed_at' => 'datetime', // <--- AJOUTER CETTE LIGNE
        ];
    }
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => strtolower(trim($value))
        );
    }
    /* -------------------------------------------------------------------------
     | SCOPES
     |-------------------------------------------------------------------------- */

    // ✅ Récupérer tous les stagiaires
    public function scopeStagiaires($query)
    {
        return $query->where('role', 'stagiaire');
    }

    // ✅ Récupérer tous les formateurs
    public function scopeFormateurs($query)
    {
        return $query->where('role', 'formateur');
    }

    // ✅ Récupérer tous les admins
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeObservateurs($query)
    {
        return $query->where('role', 'observateur');
    }

    /* -------------------------------------------------------------------------
     | RELATIONS
     |-------------------------------------------------------------------------- */

    // ✅ Groupes où l'utilisateur est stagiaire
    public function groupesStagiaire()
    {
        return $this->belongsToMany(Group::class, 'group_user', 'user_id', 'group_id')
                    ->withPivot('role_in_group')
                    ->wherePivot('role_in_group', 'stagiaire');
    }

    // ✅ Groupes où l'utilisateur est formateur (via table pivot OU colonne instructor_id sur groups)
    public function groupesFormateur()
    {
        return $this->belongsToMany(Group::class, 'group_user', 'user_id', 'group_id')
                    ->withPivot('role_in_group')
                    ->wherePivot('role_in_group', 'formateur');
    }

    public function groupesObserve()
    {
        return $this->belongsToMany(Group::class, 'group_user', 'user_id', 'group_id')
            ->withPivot('role_in_group')
            ->wherePivot('role_in_group', 'observateur');
    }

    // ✅ Si dans ta table groups tu as une colonne instructor_id
    public function groupesEncadres()
    {
        return $this->hasMany(Group::class, 'instructor_id');
    }

    /* -------------------------------------------------------------------------
     | ACCESSORS (Optionnel)
     |-------------------------------------------------------------------------- */

    // ✅ Affichage plus friendly du statut
    public function getStatutAttribute()
    {
        return $this->status ? 'Actif' : 'Inactif';
    }

    public function hasValidAssociationMembership(): bool
    {
        if ($this->role !== 'formateur') {
            return true;
        }

        if ($this->adhesion_status !== 'active') {
            return false;
        }

        return $this->adhesion_valid_until === null
            || $this->adhesion_valid_until->greaterThanOrEqualTo(today());
    }

    public function associationGraceEndsAt()
    {
        return $this->created_at?->copy()->addMonth();
    }

    public function hasActiveAssociationGracePeriod(): bool
    {
        if ($this->role !== 'formateur') {
            return true;
        }

        if ($this->adhesion_status !== 'pending') {
            return false;
        }

        return $this->associationGraceEndsAt()?->isFuture() ?? false;
    }

    public function canUsePlatformWithAssociationPolicy(): bool
    {
        return $this->hasValidAssociationMembership()
            || $this->hasActiveAssociationGracePeriod();
    }
    // ✅ Helper pour savoir si l'utilisateur doit changer son mot de passe
    public function getMustChangePasswordAttribute(): bool
    {
        return is_null($this->password_changed_at);
    }
    // Ce stagiaire appartient à un formateur
    public function formateur()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    // Ce formateur a plusieurs stagiaires
    public function stagiaires()
    {
        return $this->hasMany(User::class, 'formateur_id');
    }
    public function progressions()
    {
        return $this->hasMany(\App\Models\Progression::class);
    }

    public function hasCompleted($lectureId)
    {
        return $this->progressions()->where('lecture_id', $lectureId)->exists();
    }

    // Commentaire a plusieurs stagiaires
    public function lessonFeedbacks()
    {
        return $this->hasMany(LessonFeedback::class);
    }

    public function pilotageAssignedTasks()
    {
        return $this->hasMany(PilotTask::class, 'responsible_id');
    }

    public function pilotageCreatedProjects()
    {
        return $this->hasMany(PilotProject::class, 'created_by');
    }

    public function pilotageNotificationPreference()
    {
        return $this->hasOne(PilotNotificationPreference::class, 'user_id');
    }

    private function cleanupOwnedGroupsAndLinkedStagiaires(): void
    {
        $formateurId = (int) $this->id;

        DB::transaction(function () use ($formateurId): void {
            $candidateStagiaireIds = User::query()
                ->where('role', 'stagiaire')
                ->where(function ($query) use ($formateurId): void {
                    $query->where('formateur_id', $formateurId)
                        ->orWhereHas('groupesStagiaire', function ($groupQuery) use ($formateurId): void {
                            $groupQuery->where('groups.instructor_id', $formateurId);
                        });
                })
                ->pluck('id');

            Group::query()
                ->where('instructor_id', $formateurId)
                ->delete();

            foreach ($candidateStagiaireIds as $stagiaireId) {
                $stagiaire = User::query()
                    ->whereKey($stagiaireId)
                    ->where('role', 'stagiaire')
                    ->first();

                if (! $stagiaire) {
                    continue;
                }

                $otherGroupInstructorId = DB::table('group_user')
                    ->join('groups', 'groups.id', '=', 'group_user.group_id')
                    ->where('group_user.user_id', $stagiaire->id)
                    ->where('group_user.role_in_group', 'stagiaire')
                    ->where('groups.instructor_id', '<>', $formateurId)
                    ->orderBy('groups.id')
                    ->value('groups.instructor_id');

                $hasAnotherDirectFormateur = ! empty($stagiaire->formateur_id)
                    && (int) $stagiaire->formateur_id !== $formateurId;

                if ($otherGroupInstructorId || $hasAnotherDirectFormateur) {
                    if ((int) $stagiaire->formateur_id === $formateurId) {
                        $stagiaire->formateur_id = $otherGroupInstructorId ?: null;
                        $stagiaire->save();
                    }

                    continue;
                }

                $stagiaire->delete();
            }
        });
    }

    private function cleanupRelatedStagiaireData(): void
    {
        $stagiaireId = (int) $this->id;

        $singleUserTables = [
            'group_user',
            'progressions',
            'scorm_scores',
            'scorm_results',
            'scorm_interactions',
            'scorm_evaluation_results',
            'scorm_evaluation_scores',
            'scorm_evaluation_interactions',
            'quiz_attempts',
            'lesson_feedbacks',
            'video_segment_trackings',
            'pilot_subscriptions',
            'pilot_notification_preferences',
            'word_cloud_entries',
            'learning_objectives',
            'sessions',
            'activity_journal_entries',
        ];

        foreach ($singleUserTables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::table($table)
                ->where('user_id', $stagiaireId)
                ->delete();
        }

        if (Schema::hasTable('module_completion_notifications')) {
            DB::table('module_completion_notifications')
                ->where('stagiaire_id', $stagiaireId)
                ->orWhere('recipient_id', $stagiaireId)
                ->delete();
        }

        if (Schema::hasTable('notifications')) {
            DB::table('notifications')
                ->where('notifiable_type', self::class)
                ->where('notifiable_id', $stagiaireId)
                ->delete();
        }
    }

}
