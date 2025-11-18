<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si l'utilisateur n'est pas connecté OU
        // s'il n'a PAS le rôle 'Super Admin'...
        if (! $request->user() || ! $request->user()->hasRole('Super Admin')) {
            abort(403, 'ACTION NON AUTORISÉE.');
        }

        // S'il est admin, on le laisse passer.
        return $next($request);
    }
}