<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_name',
        'category_description',
        'category_slug',
        'category_image',
    ];

    public function subcategories()
    {
        return $this->hasMany(SubCategory::class);
    }

    public function getCategoryImageUrlAttribute(): string
    {
        $default = asset('upload/category_images/NoImage.png');
        $rawPath = trim((string) $this->category_image);

        if ($rawPath === '') {
            return $default;
        }

        if (Str::startsWith($rawPath, ['http://', 'https://', 'data:'])) {
            return $rawPath;
        }

        $normalizedPath = ltrim($rawPath, '/');
        if (Str::startsWith($normalizedPath, 'storage/')) {
            $normalizedPath = Str::after($normalizedPath, 'storage/');
        }

        if (Storage::disk('public')->exists($normalizedPath)) {
            return route('media.storage', ['path' => $normalizedPath]);
        }

        return asset('storage/' . $normalizedPath);
    }
}
