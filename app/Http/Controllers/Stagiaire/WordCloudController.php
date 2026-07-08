<?php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Controller;
use App\Models\WordCloud;
use Illuminate\Http\JsonResponse;

class WordCloudController extends Controller
{
    public function notificationStatus(): JsonResponse
    {
        $user = auth()->user();

        $activeWordCloud = WordCloud::query()
            ->where('is_active', true)
            ->whereHas('group', function ($query) use ($user): void {
                $query
                    ->active()
                    ->whereHas('students', fn ($studentQuery) => $studentQuery->where('users.id', (int) $user->id));
            })
            ->with('group')
            ->latest('opened_at')
            ->latest('id')
            ->first();

        return response()->json([
            'has_active_wordcloud' => (bool) $activeWordCloud,
            'title' => $activeWordCloud?->title,
            'group_name' => $activeWordCloud?->group?->name,
            'access_code' => $activeWordCloud?->access_code,
            'opened_at_human' => $activeWordCloud?->opened_at?->diffForHumans(),
            'join_url' => $activeWordCloud
                ? route('wordcloud.join.code', ['code' => $activeWordCloud->access_code])
                : null,
        ]);
    }
}
