<?php

use App\Models\Agence;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleKey;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
});

function keyAdminAndVehicle(): array
{
    $agence = Agence::factory()->create();
    $user = User::factory()->create(['agence_id' => $agence->id]);
    $user->assignRole('Super Admin');
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Clio', 'immatriculation' => 'AB-KEY',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    return [$user, $v];
}

it('ajoute une clé', function (): void {
    [$user, $v] = keyAdminAndVehicle();

    $response = $this->actingAs($user)->post(route('admin.keys.store'), [
        'vehicle_id' => $v->id,
        'emplacement_clef' => 'Tiroir A',
    ]);

    $response->assertRedirect(route('admin.vehicles.edit', $v->id));
    $this->assertDatabaseHas('vehicle_keys', ['vehicle_id' => $v->id, 'emplacement_clef' => 'Tiroir A']);
});

it('affiche le formulaire d\'édition d\'une clé', function (): void {
    [$user, $v] = keyAdminAndVehicle();
    $key = VehicleKey::create(['vehicle_id' => $v->id, 'emplacement_clef' => 'Box 1']);

    $response = $this->actingAs($user)->get(route('admin.keys.edit', $key));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Admin/Keys/Edit')->has('vehicleKey'));
});

it('met à jour une clé', function (): void {
    [$user, $v] = keyAdminAndVehicle();
    $key = VehicleKey::create(['vehicle_id' => $v->id, 'emplacement_clef' => 'Box 1']);

    $response = $this->actingAs($user)->put(route('admin.keys.update', $key), [
        'emplacement_clef' => 'Box 2',
    ]);

    $response->assertRedirect(route('admin.vehicles.edit', $v->id));
    $key->refresh();
    expect($key->emplacement_clef)->toBe('Box 2');
});

it('supprime une clé', function (): void {
    [$user, $v] = keyAdminAndVehicle();
    $key = VehicleKey::create(['vehicle_id' => $v->id, 'emplacement_clef' => 'Box 1']);

    $response = $this->actingAs($user)->delete(route('admin.keys.destroy', $key->id));

    $response->assertRedirect(route('admin.vehicles.edit', $v->id));
    $this->assertDatabaseMissing('vehicle_keys', ['id' => $key->id]);
});
