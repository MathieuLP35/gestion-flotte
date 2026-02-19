<?php

use Illuminate\Support\Facades\Vite;
use App\Providers\AppServiceProvider;

it('configures vite prefetching with correct concurrency', function () {
    Vite::spy();

    // On instancie et on boot manuellement pour le test
    $provider = new AppServiceProvider(app());
    $provider->boot();

    // Utilisation d'une closure pour vérifier l'argument nommé 'concurrency'
    Vite::shouldHaveReceived('prefetch')
        ->once()
        ->withArgs(function ($concurrency) {
            return $concurrency === 3;
        });
});
