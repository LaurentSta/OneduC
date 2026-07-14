<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TrackSessionTime
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            session()->start();

            $userId = Auth::id();

            if (session()->has('last_activity_time')) {
                $start = session()->pull('last_activity_time');
                $startTimestamp = $start instanceof \Carbon\Carbon ? $start->timestamp : (int) $start;
                $duration = time() - $startTimestamp;

                if ($duration > 0 && $duration < 3600) {
                    Auth::user()->increment('total_site_time', $duration);
                } else {
                    Log::warning("[⛔ INCOHÉRENCE] Durée $duration ignorée pour user: $userId");
                }
            }
        }

        return $next($request);
    }

    public function terminate($request, $response)
    {
        if (Auth::check()) {
            session()->start();
            session(['last_activity_time' => time()]);
        }
    }
}
