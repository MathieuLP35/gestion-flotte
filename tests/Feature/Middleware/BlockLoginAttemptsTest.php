<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

it('passe la requête si pas d\'email', function () {
    $response = $this->post(route('login'), [
        'password' => 'password',
    ]);

    // La validation échouera sur email requis, mais le middleware BlockLoginAttempts
    // n'est pas utilisé sur la route login dans auth.php. On teste le middleware en unitaire.
    // Pour un test d'intégration, on pourrait attacher le middleware à une route de test.
    // Ici on teste le middleware en l'invoquant directement.
    $middleware = new \App\Http\Middleware\BlockLoginAttempts;
    $request = \Illuminate\Http\Request::create('/test', 'POST', []);
    $next = fn ($r) => new \Illuminate\Http\Response('ok');
    $result = $middleware->handle($request, $next);
    expect($result->getContent())->toBe('ok');
});

it('bloque si le compte est verrouillé', function () {
    $middleware = new \App\Http\Middleware\BlockLoginAttempts;
    $request = \Illuminate\Http\Request::create('/test', 'POST', ['email' => 'locked@test.com']);
    Cache::put('login_locked_locked@test.com', now()->addMinutes(30), now()->addMinutes(30));

    $next = fn ($r) => new \Illuminate\Http\Response('ok');
    $result = $middleware->handle($request, $next);

    expect($result->isRedirection())->toBeTrue();
    Cache::forget('login_locked_locked@test.com');
});

it('passe si la clé de verrouillage a expiré', function () {
    $middleware = new \App\Http\Middleware\BlockLoginAttempts;
    $request = \Illuminate\Http\Request::create('/test', 'POST', ['email' => 'expired@test.com']);
    Cache::put('login_locked_expired@test.com', now()->subMinute(), now()->subMinute());

    $next = fn ($r) => new \Illuminate\Http\Response('ok');
    $result = $middleware->handle($request, $next);

    expect($result->getContent())->toBe('ok');
    Cache::forget('login_locked_expired@test.com');
});
