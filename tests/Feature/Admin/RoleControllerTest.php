<?php

use App\Models\Agence;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->seed(PermissionSeeder::class);
});

function roleAdminUser(): User
{
    $agence = Agence::factory()->create();
    $u = User::factory()->create(['agence_id' => $agence->id]);
    $u->assignRole('Super Admin');

    return $u;
}

it('affiche la liste des rôles', function (): void {
    $response = $this->actingAs(roleAdminUser())->get(route('admin.roles.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Roles/Index')
        ->has('roles')
    );
});

it('affiche le formulaire de création de rôle', function (): void {
    $response = $this->actingAs(roleAdminUser())->get(route('admin.roles.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Roles/Create')
        ->has('permissions')
    );
});

it('crée un rôle', function (): void {
    $response = $this->actingAs(roleAdminUser())->post(route('admin.roles.store'), [
        'name' => 'Editeur',
        'permissions' => ['roles.view'],
    ]);

    $response->assertRedirect(route('admin.roles.index'));
    $this->assertDatabaseHas('roles', ['name' => 'Editeur']);
});

it('affiche le formulaire d\'édition d\'un rôle', function (): void {
    $role = Role::create(['name' => 'RoleEdit', 'guard_name' => 'web']);

    $response = $this->actingAs(roleAdminUser())->get(route('admin.roles.edit', $role));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Roles/Edit')
        ->has('role')
        ->has('permissions')
    );
});

it('met à jour un rôle', function (): void {
    $role = Role::create(['name' => 'RoleUpd', 'guard_name' => 'web']);

    $response = $this->actingAs(roleAdminUser())->put(route('admin.roles.update', $role), [
        'name' => 'RoleUpdModifié',
        'permissions' => [],
    ]);

    $response->assertRedirect(route('admin.roles.index'));
    $role->refresh();
    expect($role->name)->toBe('RoleUpdModifié');
});

it('supprime un rôle', function (): void {
    $role = Role::create(['name' => 'RoleDel', 'guard_name' => 'web']);

    $response = $this->actingAs(roleAdminUser())->delete(route('admin.roles.destroy', $role));

    $response->assertRedirect(route('admin.roles.index'));
    $this->assertDatabaseMissing('roles', ['name' => 'RoleDel']);
});
