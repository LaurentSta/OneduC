<?php

namespace App\Http\Controllers\Concerns;

use App\Models\GroupWhiteboard;
use App\Models\GroupWhiteboardItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

trait InteractsWithGroupWhiteboards
{
    protected function whiteboardSnapshotResponse(GroupWhiteboard $whiteboard): JsonResponse
    {
        return response()->json($whiteboard->fresh()->snapshot());
    }

    protected function upsertWhiteboardItem(Request $request, GroupWhiteboard $whiteboard): JsonResponse
    {
        $validated = $request->validate([
            'client_uuid' => ['required', 'string', 'max:80'],
            'type' => ['required', Rule::in(['note', 'text', 'rectangle', 'circle', 'stroke'])],
            'x' => ['required', 'numeric', 'min:-4000', 'max:8000'],
            'y' => ['required', 'numeric', 'min:-4000', 'max:8000'],
            'width' => ['required', 'numeric', 'min:1', 'max:8000'],
            'height' => ['required', 'numeric', 'min:1', 'max:8000'],
            'rotation' => ['nullable', 'numeric', 'min:-360', 'max:360'],
            'z_index' => ['nullable', 'integer', 'min:0', 'max:200000'],
            'payload' => ['nullable', 'array'],
            'style' => ['nullable', 'array'],
        ]);

        $actor = auth()->user();

        $item = DB::transaction(function () use ($validated, $whiteboard, $actor): GroupWhiteboardItem {
            $existing = GroupWhiteboardItem::query()
                ->where('group_whiteboard_id', $whiteboard->id)
                ->where('client_uuid', $validated['client_uuid'])
                ->first();

            $item = GroupWhiteboardItem::query()->updateOrCreate(
                [
                    'group_whiteboard_id' => $whiteboard->id,
                    'client_uuid' => $validated['client_uuid'],
                ],
                [
                    'type' => $validated['type'],
                    'x' => $validated['x'],
                    'y' => $validated['y'],
                    'width' => $validated['width'],
                    'height' => $validated['height'],
                    'rotation' => (float) ($validated['rotation'] ?? 0),
                    'z_index' => (int) ($validated['z_index'] ?? 0),
                    'payload' => $validated['payload'] ?? [],
                    'style' => $validated['style'] ?? [],
                    'created_by' => $existing?->created_by ?: $actor?->id,
                    'updated_by' => $actor?->id,
                ]
            );

            $whiteboard->refresh();
            $whiteboard->bump($actor);

            return $item->fresh();
        });

        $whiteboard->refresh();

        return response()->json([
            'item' => $item->toWhiteboardArray(),
            'version' => (int) $whiteboard->version,
            'updated_at' => $whiteboard->updated_at?->toIso8601String(),
        ]);
    }

    protected function destroyWhiteboardItem(GroupWhiteboard $whiteboard, GroupWhiteboardItem $item): JsonResponse
    {
        abort_unless((int) $item->group_whiteboard_id === (int) $whiteboard->id, 404);

        DB::transaction(function () use ($whiteboard, $item): void {
            $item->delete();
            $whiteboard->refresh();
            $whiteboard->bump(auth()->user());
        });

        $whiteboard->refresh();

        return response()->json([
            'version' => (int) $whiteboard->version,
            'updated_at' => $whiteboard->updated_at?->toIso8601String(),
        ]);
    }

    protected function clearWhiteboard(GroupWhiteboard $whiteboard): JsonResponse
    {
        DB::transaction(function () use ($whiteboard): void {
            $whiteboard->items()->delete();
            $whiteboard->refresh();
            $whiteboard->bump(auth()->user());
        });

        $whiteboard->refresh();

        return response()->json([
            'version' => (int) $whiteboard->version,
            'updated_at' => $whiteboard->updated_at?->toIso8601String(),
        ]);
    }
}
