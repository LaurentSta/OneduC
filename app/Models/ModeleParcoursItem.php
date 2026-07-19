<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModeleParcoursItem extends Model
{
    public const TYPE_MODULE = 'module';

    public const TYPE_OUTIL = 'outil';

    protected $table = 'modele_parcours_items';

    protected $fillable = [
        'modele_parcours_id',
        'position',
        'type',
        'module_id',
        'outil',
        'configuration',
    ];

    protected $casts = [
        'position' => 'integer',
        'configuration' => 'array',
    ];

    public function modele(): BelongsTo
    {
        return $this->belongsTo(ModeleParcours::class, 'modele_parcours_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function estModule(): bool
    {
        return $this->type === self::TYPE_MODULE;
    }

    public function estOutil(): bool
    {
        return $this->type === self::TYPE_OUTIL;
    }
}
