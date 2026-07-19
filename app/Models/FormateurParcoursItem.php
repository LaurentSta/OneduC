<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormateurParcoursItem extends Model
{
    protected $table = 'formateur_parcours_items';

    protected $fillable = [
        'formateur_parcours_id',
        'position',
        'type',
        'module_id',
        'outil',
        'configuration',
        'wc_title',
        'wc_questions',
        'wc_duration',
        'poll_questions',
        'poll_duration',
    ];

    protected $casts = [
        'wc_questions' => 'array',
        'poll_questions' => 'array',
        'configuration' => 'array',
    ];

    public function parcours(): BelongsTo
    {
        return $this->belongsTo(FormateurParcours::class, 'formateur_parcours_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function isModule(): bool
    {
        return $this->type === 'module';
    }

    public function isWordCloud(): bool
    {
        return $this->type === 'wordcloud';
    }

    public function isPoll(): bool
    {
        return $this->type === 'poll';
    }

    public function isOutil(): bool
    {
        return $this->type === 'outil';
    }
}
