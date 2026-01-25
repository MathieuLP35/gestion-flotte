<?php

use App\Models\Agence;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

it('retourne 401 pour /api/translations si non authentifié', function () {
    $response = $this->getJson('/api/translations');
    $response->assertStatus(401);
});

it('retourne les clés permissions.* pour un utilisateur authentifié', function () {
    $agence = Agence::factory()->create();
    $user = User::factory()->create(['agence_id' => $agence->id]);
    $user->assignRole('Super Admin');

    $response = $this->actingAs($user)->getJson('/api/translations');

    $response->assertOk();
    $data = $response->json();
    expect($data)->toBeArray();
    $invalid = array_filter(array_keys($data), fn ($k) => !str_starts_with((string) $k, 'permissions.'));
    expect($invalid)->toBeEmpty();
});

it('retourne uniquement les clés demandées avec ?keys=', function () {
    $agence = Agence::factory()->create();
    $user = User::factory()->create(['agence_id' => $agence->id]);
    $user->assignRole('Super Admin');

    $response = $this->actingAs($user)->getJson('/api/translations?keys=permissions.roles.view,permissions.admin.view');

    $response->assertOk();
    $data = $response->json();
    expect($data)->toHaveKeys(['permissions.roles.view', 'permissions.admin.view']);
    expect($data)->toHaveCount(2);
});
