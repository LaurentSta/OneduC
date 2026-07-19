<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class FormateurParcours extends Model
{
    protected $table = 'formateur_parcours';

    protected $fillable = ['formateur_id', 'modele_parcours_id', 'title', 'description'];

    public function formateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function modeleSource(): BelongsTo
    {
        return $this->belongsTo(ModeleParcours::class, 'modele_parcours_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FormateurParcoursItem::class, 'formateur_parcours_id')
            ->orderBy('position');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'formateur_parcours_id');
    }
}
