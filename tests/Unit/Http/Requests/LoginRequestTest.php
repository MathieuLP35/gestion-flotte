<?php

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;

it('retourne authorize à true', function (): void {
    $r = LoginRequest::createFrom(Request::create('/login', 'POST', ['email' => 'a@b.com', 'password' => 'p']));
    $r->setContainer(app());
    app()->instance('request', $r);

    expect($r->authorize())->toBeTrue();
});

it('retourne les règles de validation', function (): void {
    $r = LoginRequest::createFrom(Request::create('/login', 'POST', ['email' => 'a@b.com', 'password' => 'p']));
    $r->setContainer(app());
    app()->instance('request', $r);

    $rules = $r->rules();
    expect($rules)->toHaveKey('email')->toHaveKey('password');
});

it('throttleKey contient l\'email et l\'ip', function (): void {
    $base = Request::create('/login', 'POST', ['email' => 'throttle@test.com', 'password' => 'p']);
    $r = LoginRequest::createFrom($base);
    $r->setContainer(app());
    app()->instance('request', $r);

    $key = $r->throttleKey();
    expect($key)->toContain('throttle@test.com');
});
