<?php

namespace App\Http\Middleware;

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
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            
            'auth' => [
                'user' => $request->user(),
                
                // ↓↓↓ LA PARTIE MANQUANTE EST ICI ↓↓↓
                // On doit envoyer les permissions et rôles au front-end
                'permissions' => $request->user() ? $request->user()->getAllPermissions()->pluck('name') : [],
                'roles' => $request->user() ? $request->user()->getRoleNames() : [],
            ],
            
            // ↓↓↓ ASSUREZ-VOUS D'AVOIR AUSSI CE BLOC ↓↓↓
            // (Il est crucial pour les messages de succès/erreur)
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],

            // Votre bloc de traduction est parfait.
            'translations' => function () {
                // Charge le fichier fr.json et l'envoie au front
                return file_exists(resource_path('lang/fr.json'))
                    ? json_decode(file_get_contents(resource_path('lang/fr.json')), true)
                    : [];
            },
        ];
    }
}
