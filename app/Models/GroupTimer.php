<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupTimer extends Model
{
    protected $fillable = [
        'group_id',
        'label',
        'duration_seconds',
        'status',
        'started_at',
        'elapsed_seconds',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'elapsed_seconds' => 'integer',
        'started_at' => 'datetime',
    ];

    public static function ensureForGroup(Group $group, ?User $actor = null): self
    {
        return static::query()->firstOrCreate(
            ['group_id' => $group->id],
            [
                'label' => null,
                'duration_seconds' => 1500,
                'status' => 'idle',
                'created_by' => $actor?->id,
                'updated_by' => $actor?->id,
            ]
        );
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function getRemainingSecondsAttribute(): int
    {
        if ($this->status === 'idle') {
            return (int) $this->duration_seconds;
        }

        if ($this->status === 'finished') {
            return 0;
        }

        $elapsed = (int) $this->elapsed_seconds;

        if ($this->status === 'running' && $this->started_at) {
            $elapsed += (int) now()->diffInSeconds($this->started_at);
        }

        return max(0, (int) $this->duration_seconds - $elapsed);
    }

    public function resolveFinished(): void
    {
        if ($this->status === 'running' && $this->remaining_seconds === 0) {
            $this->forceFill(['status' => 'finished'])->save();
        }
    }

    public function start(?User $actor = null): void
    {
        if ($this->status === 'running') return;

        $this->forceFill([
            'status' => 'running',
            'started_at' => now(),
            'updated_by' => $actor?->id,
        ])->save();
    }

    public function pause(?User $actor = null): void
    {
        if ($this->status !== 'running') return;

        $elapsed = (int) $this->elapsed_seconds + (int) now()->diffInSeconds($this->started_at);

        $this->forceFill([
            'status' => 'paused',
            'started_at' => null,
            'elapsed_seconds' => $elapsed,
            'updated_by' => $actor?->id,
        ])->save();
    }

    public function reset(?User $actor = null): void
    {
        $this->forceFill([
            'status' => 'idle',
            'started_at' => null,
            'elapsed_seconds' => 0,
            'updated_by' => $actor?->id,
        ])->save();
    }

    public function configure(int $durationSeconds, ?string $label, ?User $actor = null): void
    {
        $this->forceFill([
            'duration_seconds' => max(30, $durationSeconds),
            'label' => $label,
            'status' => 'idle',
            'started_at' => null,
            'elapsed_seconds' => 0,
            'updated_by' => $actor?->id,
        ])->save();
    }

    public function toStatusArray(): array
    {
        $this->resolveFinished();
        $this->refresh();

        return [
            'status' => $this->status,
            'duration_seconds' => (int) $this->duration_seconds,
            'remaining_seconds' => $this->remaining_seconds,
            'label' => $this->label,
        ];
    }
}
