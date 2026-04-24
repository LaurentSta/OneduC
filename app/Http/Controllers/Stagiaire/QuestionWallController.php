<?php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Controller;
use App\Models\QuestionWall;
use Illuminate\Http\JsonResponse;

class QuestionWallController extends Controller
{
    public function notificationStatus(): JsonResponse
    {
        $user = auth()->user();

        $wall = QuestionWall::query()
            ->where('is_active', true)
            ->whereHas('group.students', function ($query) use ($user): void {
                $query->where('users.id', (int) $user->id);
            })
            ->with('group')
            ->latest('updated_at')
            ->first();

        return response()->json([
            'has_active_question_wall' => ! is_null($wall),
            'title' => $wall?->title,
            'group_name' => $wall?->group?->name,
            'access_code' => $wall?->access_code,
            'join_url' => $wall
                ? route('questions.join.code', ['code' => $wall->access_code])
                : null,
        ]);
    }
}
