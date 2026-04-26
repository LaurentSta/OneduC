<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;

class LessonResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'lecture_id',
        'module_id',
        'title',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'is_visible_to_stagiaire',
        'position',
    ];

    protected $casts = [
        'is_visible_to_stagiaire' => 'boolean',
        'file_size' => 'integer',
        'position' => 'integer',
    ];

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(ModuleLecture::class, 'lecture_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function getPublicUrlAttribute(): string
    {
        return Route::has('media.storage')
            ? route('media.storage', ['path' => $this->file_path], false)
            : '';
    }

    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo((string) $this->original_name, PATHINFO_EXTENSION));
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }
}
