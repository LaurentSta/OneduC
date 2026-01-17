<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScormPackageVersion extends Model
{
    protected $fillable = [
        'scorm_package_id',
        'version',
        'folder',
        'index_path',
        'size_bytes',
        'api_injected',
        'imported_at',
    ];

    protected $casts = [
        'api_injected' => 'boolean',
        'imported_at' => 'datetime',
        'size_bytes'   => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(ScormPackage::class, 'scorm_package_id');
    }

    public function lectures(): HasMany
    {
        return $this->hasMany(ModuleLecture::class, 'scorm_package_version_id');
    }
}
