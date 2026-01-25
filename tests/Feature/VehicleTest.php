<?php

use App\Models\Agence;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    foreach (['vehicles.view', 'vehicles.create', 'vehicles.edit', 'vehicles.delete'] as $name) {
        Permission::firstOrCreate(['name' => $name]);
    }
    $role->givePermissionTo(['vehicles.view', 'vehicles.create', 'vehicles.edit', 'vehicles.delete']);
});

it('allows admin to create a vehicle', function () {
    $agence = Agence::factory()->create();
    $user = User::factory()->create(['agence_id' => $agence->id]);
    $user->assignRole('Super Admin');

    $response = $this->actingAs($user)->post(route('admin.vehicles.store'), [
        'modele' => 'Peugeot 208',
        'immatriculation' => 'AB-123-CD',
        'km_initial' => 0,
        'emplacement' => 'Garage A',
        'nbr_places' => 5,
        'nbr_cles' => 1,
        'energie' => 'essence',
    ]);

    $response->assertRedirect(route('admin.vehicles.availability'));

    $this->assertDatabaseHas('vehicles', [
        'modele' => 'Peugeot 208',
        'immatriculation' => 'AB-123-CD',
        'agence_id' => $agence->id,
    ]);
});
