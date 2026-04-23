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
        'active_entry_ids',
        'picks',
        'current_pick_id',
        'spun_at',
    ];

    protected $casts = [
        'entries'           => 'array',
        'active_entry_ids'  => 'array',
        'picks'             => 'array',
        'spun_at'           => 'datetime',
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

        return collect($this->entries ?? [])->firstWhere('id', (int) $this->current_pick_id);
    }

    public function entryIds(): array
    {
        return collect($this->entries ?? [])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function activeEntryIds(): array
    {
        $entryIds = $this->entryIds();

        if ($this->active_entry_ids === null) {
            return $entryIds;
        }

        $activeSet = collect($this->active_entry_ids)
            ->map(fn ($id) => (int) $id)
            ->unique();

        return collect($entryIds)
            ->filter(fn ($id) => $activeSet->contains($id))
            ->values()
            ->all();
    }

    public function activeEntries(): array
    {
        $activeIds = $this->activeEntryIds();

        return collect($this->entries ?? [])
            ->filter(fn ($entry) => in_array((int) ($entry['id'] ?? 0), $activeIds, true))
            ->values()
            ->all();
    }

    public function availableEntries(): array
    {
        $pickedIds = collect($this->picks ?? [])
            ->map(fn ($id) => (int) $id)
            ->all();

        return collect($this->activeEntries())
            ->filter(fn ($entry) => !in_array((int) $entry['id'], $pickedIds, true))
            ->values()
            ->all();
    }

    public function isExhausted(): bool
    {
        return count($this->availableEntries()) === 0;
    }

    public function stateKey(): string
    {
        $picksHash = md5(json_encode(array_values($this->picks ?? [])));
        $activeHash = md5(json_encode($this->activeEntryIds()));

        return implode('|', [
            $this->current_pick_id ?? 'null',
            $picksHash,
            $this->spun_at?->toISOString() ?? 'null',
            $activeHash,
        ]);
    }
}
