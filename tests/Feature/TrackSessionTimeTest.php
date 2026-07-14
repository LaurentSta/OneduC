<?php

use App\Http\Middleware\TrackSessionTime;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

it('increments total_site_time exactly once per request cycle and does not log a false warning', function () {
    $user = User::factory()->create(['role' => 'stagiaire', 'total_site_time' => 0]);
    $this->actingAs($user);

    $middleware = app(TrackSessionTime::class);
    $request = Request::create('/stagiaire');
    $request->setLaravelSession(app('session.store'));

    session()->start();
    session(['last_activity_time' => time() - 5]);

    Log::spy();

    $middleware->handle($request, fn ($req) => response('ok'));
    $response = response('ok');
    $middleware->terminate($request, $response);

    expect($user->fresh()->total_site_time)->toBe(5);
    Log::shouldNotHaveReceived('warning');

    // terminate() marque la fin de la requete comme nouveau point de
    // reference pour la prochaine requete.
    expect(session('last_activity_time'))->toBeGreaterThanOrEqual(time() - 1);
});
