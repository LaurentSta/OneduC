<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seance extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'formateur_id',
        'date',
        'creneau',
        'heure_debut',
        'heure_fin',
        'titre',
        'statut',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'date' => 'date',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function formateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function presences(): HasMany
    {
        return $this->hasMany(SeancePresence::class);
    }

    public function getReferenceAttribute(): string
    {
        return 'EMG-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
