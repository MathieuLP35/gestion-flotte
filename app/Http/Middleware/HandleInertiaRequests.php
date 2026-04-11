<?php

namespace App\Http\Middleware;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     * Utilise des Resources pour limiter les données exposées (pas de pivot,
     * timestamps, password, etc.) et n'envoie que les traductions essentielles.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),

            'auth' => [
                'user' => $user ? (new UserResource($user))->resolve() : null,
                'permissions' => $user ? $user->getAllPermissions()->pluck('name')->values()->all() : [],
                'roles' => $user ? $user->getRoleNames()->all() : [],
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],

            'locale' => fn () => app()->getLocale(),

            'translations' => function () {
                $path = resource_path('lang/' . app()->getLocale() . '.json');
                return file_exists($path) ? json_decode(file_get_contents($path), true) : [];
            },

            'appVersion' => config('app.version', 'v0.1.0-alpha'),
        ];
    }
}
