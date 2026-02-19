<?php

use App\Models\AllowedDomain;

it('renders the registration screen', function (): void {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

it('allows new users to register', function (): void {
    AllowedDomain::create(['name' => 'example.com']);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
