<?php

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Vite;

it('configures vite prefetching with correct concurrency', function (): void {
    Vite::spy();

    // On instancie et on boot manuellement pour le test
    $provider = new AppServiceProvider(app());
    $provider->boot();

    // Utilisation d'une closure pour vérifier l'argument nommé 'concurrency'
    Vite::shouldHaveReceived('prefetch')
        ->once()
        ->withArgs(function ($concurrency): bool {
            return $concurrency === 3;
        });
});
