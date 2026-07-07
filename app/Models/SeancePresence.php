<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SeancePresence extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('signature')
            ->useDisk('local')
            ->singleFile()
            ->acceptsMimeTypes(['image/png']);
    }

    protected $fillable = [
        'seance_id',
        'user_id',
        'stagiaire_nom_snapshot',
        'statut',
        'signature_type',
        'motif_absence',
        'updated_by',
    ];

    public function seance(): BelongsTo
    {
        return $this->belongsTo(Seance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getSignatureBase64Attribute(): ?string
    {
        $media = $this->getFirstMedia('signature');

        if (! $media) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode(file_get_contents($media->getPath()));
    }
}
