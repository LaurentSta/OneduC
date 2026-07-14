<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'is_sandbox',
        'emargement_enabled',
        'emargement_code',
        'start_date',
        'end_date',
        'temporary_password',
        'instructor_id',
        'formateur_parcours_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_sandbox' => 'boolean',
        'emargement_enabled' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'temporary_password' => 'encrypted',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function formateurParcours()
    {
        return $this->belongsTo(FormateurParcours::class, 'formateur_parcours_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id')
            ->wherePivot('role_in_group', 'stagiaire');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'group_module')
            ->withPivot('position')
            ->orderBy('group_module.position');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id');
    }

    public function coFormateurs()
    {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id')
            ->wherePivot('role_in_group', 'formateur');
    }

    public function observers()
    {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id')
            ->wherePivot('role_in_group', 'observateur');
    }

    public function scopeAccessibleByTrainer(Builder $query, int $trainerId): Builder
    {
        return $query->where(function (Builder $groupQuery) use ($trainerId): void {
            $groupQuery
                ->where('instructor_id', $trainerId)
                ->orWhereHas('coFormateurs', function (Builder $trainerQuery) use ($trainerId): void {
                    $trainerQuery->where('users.id', $trainerId);
                });
        });
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user instanceof User && (int) $this->instructor_id === (int) $user->id;
    }

    public function isAccessibleBy(?User $user): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        if ($this->isOwnedBy($user)) {
            return true;
        }

        if ($this->relationLoaded('coFormateurs')) {
            return $this->coFormateurs->contains(fn (User $trainer) => (int) $trainer->id === (int) $user->id);
        }

        return $this->coFormateurs()
            ->where('users.id', (int) $user->id)
            ->exists();
    }

    public function canManageCoFormateurs(?User $user): bool
    {
        return $this->isOwnedBy($user);
    }

    public function whiteboard()
    {
        return $this->hasOne(GroupWhiteboard::class);
    }

    public function wordClouds()
    {
        return $this->hasMany(WordCloud::class);
    }

    public function liveQuizSessions()
    {
        return $this->hasMany(LiveQuizSession::class);
    }

    public function pollSessions()
    {
        return $this->hasMany(PollSession::class);
    }

    public function trueFalseSessions()
    {
        return $this->hasMany(TrueFalseSession::class);
    }

    public function buzzerSessions()
    {
        return $this->hasMany(BuzzerSession::class);
    }

    public function componentFinderSessions()
    {
        return $this->hasMany(ComponentFinderSession::class);
    }

    public function seances()
    {
        return $this->hasMany(Seance::class);
    }
}
