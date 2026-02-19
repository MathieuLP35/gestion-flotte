<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     * @param \App\Http\Requests\Auth\LoginRequest $request
     * @return RedirectResponse
     */
    public function store(Request $request) : RedirectResponse
    {
        $email = $request->input('email');
        $attemptsKey = 'login_attempts_'.$email;
        $lockKey = 'login_locked_'.$email;

        // Vérifier si le compte est verrouillé
        if (Cache::has($lockKey)) {
            $lockedUntil = Cache::get($lockKey);
            if (Carbon::now()->lessThan($lockedUntil)) {
                throw ValidationException::withMessages([
                    'email' => ['Votre compte est temporairement bloqué. Veuillez réessayer plus tard.'],
                ]);
            } else {
                // Fin du blocage, on efface les compteurs
                Cache::forget($lockKey);
                Cache::forget($attemptsKey);
            }
        }

        try {
            $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);

            // Tenter l’authentification de l’utilisateur
            $credentials = $request->only('email', 'password');
            if (! Auth::attempt($credentials, $request->boolean('remember'))) {
                throw ValidationException::withMessages([
                    'email' => ['Les identifiants fournis sont incorrects.'],
                ]);
            }

            // Succès : reset compteur d’échecs
            Cache::forget($attemptsKey);

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));

        } catch (ValidationException $e) {
            // En cas d’échec, incrémenter le compteur
            $attempts = Cache::get($attemptsKey, 0) + 1;
            Cache::put($attemptsKey, $attempts, now()->addMinutes(15));

            if ($attempts >= 6) {
                // Bloquer compte 30 minutes
                Cache::put($lockKey, Carbon::now()->addMinutes(30), now()->addMinutes(30));
                Cache::forget($attemptsKey);

                throw ValidationException::withMessages([
                    'email' => ['Votre compte a été bloqué temporairement après 6 échecs. Veuillez réessayer dans 30 minutes.'],
                ]);
            }

            // Relancer l'exception initiale sinon
            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
