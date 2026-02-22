<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    // ✅ Autorise tous les champs dans les requêtes mass assignable
    protected $guarded = [];

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

}
