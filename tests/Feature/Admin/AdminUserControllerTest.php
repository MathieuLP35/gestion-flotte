<?php

use App\Models\Agence;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function (): void {
    $this->seed(PermissionSeeder::class);
});

it('affiche la liste des utilisateurs', function (): void {
    $agence = Agence::factory()->create();
    $admin = User::factory()->create(['agence_id' => $agence->id]);
    $admin->assignRole('Super Admin');

    $response = $this->actingAs($admin)->get(route('admin.users.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Users/Index')
        ->has('users')
    );
});

it('affiche un utilisateur', function (): void {
    $agence = Agence::factory()->create();
    $admin = User::factory()->create(['agence_id' => $agence->id]);
    $admin->assignRole('Super Admin');
    $target = User::factory()->create(['agence_id' => $agence->id, 'name' => 'Jean']);

    $this->withoutVite();
    $response = $this->actingAs($admin)->get(route('admin.users.show', $target));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Users/Show', false)
        ->where('user.id', $target->id)
    );
});

it('affiche le formulaire d\'édition d\'un utilisateur', function (): void {
    $agence = Agence::factory()->create();
    $admin = User::factory()->create(['agence_id' => $agence->id]);
    $admin->assignRole('Super Admin');
    $target = User::factory()->create(['agence_id' => $agence->id]);

    $response = $this->actingAs($admin)->get(route('admin.users.edit', $target));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Users/Edit')
        ->has('user')
        ->has('agences')
        ->has('roles')
    );
});

it('met à jour un utilisateur', function (): void {
    $agence = Agence::factory()->create();
    $admin = User::factory()->create(['agence_id' => $agence->id]);
    $admin->assignRole('Super Admin');
    $target = User::factory()->create(['agence_id' => $agence->id, 'name' => 'Old']);

    $response = $this->actingAs($admin)->put(route('admin.users.update', $target), [
        'name' => 'Nouveau Nom',
        'email' => $target->email,
        'agence_id' => $agence->id,
        'role_id' => $admin->roles->first()?->id,
    ]);

    $response->assertRedirect(route('admin.users.index'));
    $target->refresh();
    expect($target->name)->toBe('Nouveau Nom');
});

it('supprime un utilisateur', function (): void {
    $agence = Agence::factory()->create();
    $admin = User::factory()->create(['agence_id' => $agence->id]);
    $admin->assignRole('Super Admin');
    $target = User::factory()->create(['agence_id' => $agence->id]);

    $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $target));

    $response->assertRedirect(route('admin.users.index'));
    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});
