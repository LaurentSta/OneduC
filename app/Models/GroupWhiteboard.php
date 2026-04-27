<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupWhiteboard extends Model
{
    protected $fillable = [
        'group_id',
        'title',
        'settings',
        'excalidraw_data',
        'version',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'excalidraw_data' => 'array',
        'version' => 'integer',
    ];

    public static function ensureForGroup(Group $group, ?User $actor = null): self
    {
        return static::query()->firstOrCreate(
            ['group_id' => $group->id],
            [
                'title' => 'Tableau blanc - ' . $group->name,
                'settings' => [
                    'background' => 'grid',
                    'edition' => 'ouverte',
                    'stage' => ['width' => 1600, 'height' => 900],
                ],
                'created_by' => $actor?->id,
                'updated_by' => $actor?->id,
            ]
        );
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GroupWhiteboardItem::class)
            ->orderBy('z_index')
            ->orderBy('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function bump(?User $actor = null): void
    {
        $this->forceFill([
            'version' => ((int) $this->version) + 1,
            'updated_by' => $actor?->id,
        ])->save();
    }

    public function snapshot(): array
    {
        $this->loadMissing(['group', 'items']);

        return [
            'id' => (int) $this->id,
            'title' => (string) ($this->title ?? 'Tableau blanc'),
            'version' => (int) $this->version,
            'settings' => $this->settings ?? [],
            'excalidraw_data' => $this->excalidraw_data,
            'group' => [
                'id' => (int) $this->group->id,
                'name' => (string) $this->group->name,
            ],
            'items' => $this->items
                ->map(fn (GroupWhiteboardItem $item) => $item->toWhiteboardArray())
                ->values()
                ->all(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    public function saveExcalidrawData(array $elements, array $appState, ?User $actor = null): void
    {
        $this->forceFill([
            'excalidraw_data' => [
                'elements' => $elements,
                'app_state' => $appState,
            ],
            'version' => ((int) $this->version) + 1,
            'updated_by' => $actor?->id,
        ])->save();
    }
}
