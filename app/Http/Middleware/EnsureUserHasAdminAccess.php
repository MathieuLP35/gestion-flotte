<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            abort(403, 'ACTION NON AUTORISÉE.');
        }

        // Accès si : rôle Super Admin OU permission admin.view
        if ($request->user()->hasRole('Super Admin') || $request->user()->can('admin.view')) {
            return $next($request);
        }

        abort(403, 'ACTION NON AUTORISÉE.');
    }
}
