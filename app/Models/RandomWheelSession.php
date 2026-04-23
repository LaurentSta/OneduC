<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RandomWheelSession extends Model
{
    protected $fillable = [
        'formateur_id',
        'group_id',
        'access_code',
        'entries',
        'picks',
        'current_pick_id',
        'spun_at',
    ];

    protected $casts = [
        'entries' => 'array',
        'picks'   => 'array',
        'spun_at' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function formateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function currentPick(): ?array
    {
        if (!$this->current_pick_id) {
            return null;
        }
        return collect($this->entries)->firstWhere('id', $this->current_pick_id);
    }

    public function availableEntries(): array
    {
        $pickedIds = $this->picks ?? [];
        return collect($this->entries)
            ->filter(fn ($e) => !in_array($e['id'], $pickedIds))
            ->values()
            ->all();
    }

    public function isExhausted(): bool
    {
        return count($this->availableEntries()) === 0;
    }

    public function stateKey(): string
    {
        return implode('|', [
            $this->current_pick_id ?? 'null',
            count($this->picks ?? []),
            $this->spun_at?->toISOString() ?? 'null',
        ]);
    }
}
