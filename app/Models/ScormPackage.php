<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScormPackage extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'active_version_id',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(ScormPackageVersion::class, 'scorm_package_id');
    }

    public function activeVersion(): BelongsTo
    {
        return $this->belongsTo(ScormPackageVersion::class, 'active_version_id');
    }
}
