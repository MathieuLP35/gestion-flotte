<?php

use App\Models\Agence;
use App\Models\Maintenance;
use App\Models\User;
use App\Models\Vehicle;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
});

function maintenanceAdminAndVehicle(): array
{
    $agence = Agence::factory()->create();
    $user = User::factory()->create(['agence_id' => $agence->id]);
    $user->assignRole('Super Admin');
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Clio', 'immatriculation' => 'AB-MAINT',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    return [$user, $v];
}

it('ajoute une révision', function (): void {
    [$user, $v] = maintenanceAdminAndVehicle();

    $response = $this->actingAs($user)->post(route('admin.maintenances.store'), [
        'vehicle_id' => $v->id,
        'date' => '2026-01-01',
        'type' => 'revision',
        'kilometrage' => 15000,
        'cost' => 250,
        'notes' => 'RAS',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('maintenances', ['vehicle_id' => $v->id, 'kilometrage' => 15000]);
});

it('refuse d\'ajouter une maintenance pour un véhicule d\'une autre agence', function (): void {
    [$user] = maintenanceAdminAndVehicle();
    $autre = Agence::factory()->create();
    $vAutre = Vehicle::create([
        'agence_id' => $autre->id, 'modele' => 'Autre', 'immatriculation' => 'XX-999',
        'km_initial' => 0, 'emplacement' => 'Y', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $response = $this->actingAs($user)->post(route('admin.maintenances.store'), [
        'vehicle_id' => $vAutre->id,
        'date' => '2026-01-01',
        'type' => 'revision',
        'kilometrage' => 15000,
    ]);

    $response->assertForbidden();
});

it('affiche le formulaire d\'édition d\'une maintenance', function (): void {
    [$user, $v] = maintenanceAdminAndVehicle();
    $m = Maintenance::create(['vehicle_id' => $v->id, 'kilometrage' => 10000, 'type' => 'revision', 'date' => '2024-01-01']);

    $response = $this->actingAs($user)->get(route('admin.maintenances.edit', $m));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Admin/Maintenances/Edit')->has('maintenance'));
});

it('met à jour une maintenance', function (): void {
    [$user, $v] = maintenanceAdminAndVehicle();
    $m = Maintenance::create(['vehicle_id' => $v->id, 'kilometrage' => 10000, 'type' => 'revision', 'date' => '2024-01-01']);

    $response = $this->actingAs($user)->put(route('admin.maintenances.update', $m), [
        'vehicle_id' => $v->id,
        'date' => '2024-06-01',
        'kilometrage' => 20000,
        'type' => 'revision',
    ]);

    $response->assertRedirect(route('admin.maintenances.show', $v->id));
    $m->refresh();
    expect($m->kilometrage)->toBe(20000);
});

it('supprime une maintenance', function (): void {
    [$user, $v] = maintenanceAdminAndVehicle();
    $m = Maintenance::create(['vehicle_id' => $v->id, 'kilometrage' => 10000, 'type' => 'revision', 'date' => '2024-01-01']);

    $response = $this->actingAs($user)->delete(route('admin.maintenances.destroy', $m));

    $response->assertRedirect();
    $this->assertDatabaseMissing('maintenances', ['id' => $m->id]);
});
