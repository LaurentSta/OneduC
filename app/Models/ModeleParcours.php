<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModeleParcours extends Model
{
    public const STATUT_BROUILLON = 'brouillon';

    public const STATUT_PUBLIE = 'publie';

    public const STATUT_ARCHIVE = 'archive';

    protected $table = 'modeles_parcours';

    protected $fillable = [
        'auteur_admin_id',
        'titre',
        'description',
        'statut',
        'publie_le',
        'archive_le',
    ];

    protected $casts = [
        'publie_le' => 'datetime',
        'archive_le' => 'datetime',
    ];

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auteur_admin_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ModeleParcoursItem::class, 'modele_parcours_id')
            ->orderBy('position');
    }

    public function copiesFormateurs(): HasMany
    {
        return $this->hasMany(FormateurParcours::class, 'modele_parcours_id');
    }

    public function scopePublies(Builder $query): Builder
    {
        return $query->where('statut', self::STATUT_PUBLIE);
    }

    public function estBrouillon(): bool
    {
        return $this->statut === self::STATUT_BROUILLON;
    }

    public function estPublie(): bool
    {
        return $this->statut === self::STATUT_PUBLIE;
    }

    public function estArchive(): bool
    {
        return $this->statut === self::STATUT_ARCHIVE;
    }
}
