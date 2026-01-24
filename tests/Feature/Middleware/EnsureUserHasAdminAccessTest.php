<?php

use App\Models\Agence;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
});

it('refuse l\'accès à /admin pour un invité', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
});

it('refuse l\'accès à /admin pour un utilisateur sans admin', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertForbidden();
});

it('autorise l\'accès à /admin pour un Super Admin', function () {
    $agence = Agence::factory()->create();
    $user = User::factory()->create(['agence_id' => $agence->id, 'email_verified_at' => now()]);
    $user->assignRole('Super Admin');

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertOk();
});

it('autorise l\'accès à /admin pour un utilisateur avec la permission admin.view', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $role = Role::firstOrCreate(['name' => 'Editeur', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'admin.view']);
    $role->givePermissionTo('admin.view');
    $user->assignRole($role);

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertOk();
});
