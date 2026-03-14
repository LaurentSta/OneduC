<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupWhiteboardItem extends Model
{
    protected $fillable = [
        'group_whiteboard_id',
        'client_uuid',
        'type',
        'x',
        'y',
        'width',
        'height',
        'rotation',
        'z_index',
        'payload',
        'style',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'x' => 'float',
        'y' => 'float',
        'width' => 'float',
        'height' => 'float',
        'rotation' => 'float',
        'z_index' => 'integer',
        'payload' => 'array',
        'style' => 'array',
    ];

    public function whiteboard(): BelongsTo
    {
        return $this->belongsTo(GroupWhiteboard::class, 'group_whiteboard_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function toWhiteboardArray(): array
    {
        return [
            'id' => (int) $this->id,
            'client_uuid' => (string) $this->client_uuid,
            'type' => (string) $this->type,
            'x' => (float) $this->x,
            'y' => (float) $this->y,
            'width' => (float) $this->width,
            'height' => (float) $this->height,
            'rotation' => (float) $this->rotation,
            'z_index' => (int) $this->z_index,
            'payload' => $this->payload ?? [],
            'style' => $this->style ?? [],
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
