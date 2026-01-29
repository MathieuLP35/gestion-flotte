<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class BlockLoginAttempts
{
    public function handle(Request $request, Closure $next)
    {
        $email = $request->input('email');

        if (! $email) {
            return $next($request);
        }

        $lockKey = 'login_locked_'.$email;

        if (Cache::has($lockKey)) {
            $lockedUntil = Cache::get($lockKey);
            if (Carbon::now()->lessThan($lockedUntil)) {
                return redirect()->back()->withErrors(['email' => 'Votre compte est temporairement bloqué. Réessayez plus tard.']);
            } else {
                Cache::forget($lockKey);
                Cache::forget('login_attempts_'.$email);
            }
        }

        return $next($request);
    }
}
