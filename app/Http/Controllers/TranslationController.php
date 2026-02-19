<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    /**
     * Retourne les traductions demandées (ou toutes les clés permissions.* si keys absent/vide).
     * GET /api/translations?keys=permissions.roles.view,permissions.users.view
     * GET /api/translations → toutes les clés dont le nom commence par "permissions."
     */
    public function index(Request $request): JsonResponse
    {
        $path = resource_path('lang/fr.json');
        if (! file_exists($path)) {
            return response()->json([]);
        }

        $all = json_decode(file_get_contents($path), true) ?: [];
        $keysParam = $request->query('keys');

        if ($keysParam === null || $keysParam === '') {
            $filtered = array_filter(
                $all,
                fn ($key): bool => str_starts_with((string) $key, 'permissions.'),
                ARRAY_FILTER_USE_KEY
            );

            return response()->json($filtered);
        }

        $keys = array_filter(array_map('trim', explode(',', (string) $keysParam)));
        if (empty($keys)) {
            $filtered = array_filter(
                $all,
                fn ($key): bool => str_starts_with((string) $key, 'permissions.'),
                ARRAY_FILTER_USE_KEY
            );

            return response()->json($filtered);
        }

        $filtered = array_intersect_key($all, array_flip($keys));

        return response()->json($filtered);
    }
}
