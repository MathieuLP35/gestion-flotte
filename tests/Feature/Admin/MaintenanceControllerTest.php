<?php

use App\Models\Agence;
use App\Models\Maintenance;
use App\Models\User;
use App\Models\Vehicle;
use Spatie\Permission\Models\Role;

beforeEach(function () {
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

it('ajoute un seuil de maintenance', function () {
    [$user, $v] = maintenanceAdminAndVehicle();

    $response = $this->actingAs($user)->post(route('admin.maintenances.store'), [
        'vehicle_id' => $v->id,
        'km_alert_threshold' => 15000,
        'date_dernier_entretien' => '2024-01-15',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('maintenances', ['vehicle_id' => $v->id, 'km_alert_threshold' => 15000]);
});

it('refuse d\'ajouter une maintenance pour un véhicule d\'une autre agence', function () {
    [$user] = maintenanceAdminAndVehicle();
    $autre = Agence::factory()->create();
    $vAutre = Vehicle::create([
        'agence_id' => $autre->id, 'modele' => 'Autre', 'immatriculation' => 'XX-999',
        'km_initial' => 0, 'emplacement' => 'Y', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $response = $this->actingAs($user)->post(route('admin.maintenances.store'), [
        'vehicle_id' => $vAutre->id,
        'km_alert_threshold' => 10000,
        'date_dernier_entretien' => null,
    ]);

    $response->assertForbidden();
});

it('affiche le formulaire d\'édition d\'une maintenance', function () {
    [$user, $v] = maintenanceAdminAndVehicle();
    $m = Maintenance::create(['vehicle_id' => $v->id, 'km_alert_threshold' => 10000, 'date_dernier_entretien' => '2024-01-01']);

    $response = $this->actingAs($user)->get(route('admin.maintenances.edit', $m));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Admin/Maintenances/Edit')->has('maintenance')->has('vehicles'));
});

it('met à jour une maintenance', function () {
    [$user, $v] = maintenanceAdminAndVehicle();
    $m = Maintenance::create(['vehicle_id' => $v->id, 'km_alert_threshold' => 10000, 'date_dernier_entretien' => null]);

    $response = $this->actingAs($user)->put(route('admin.maintenances.update', $m), [
        'vehicle_id' => $v->id,
        'km_alert_threshold' => 20000,
        'date_dernier_entretien' => '2024-06-01',
    ]);

    $response->assertRedirect(route('admin.vehicles.edit', $v->id));
    $m->refresh();
    expect($m->km_alert_threshold)->toBe(20000);
});

it('supprime une maintenance', function () {
    [$user, $v] = maintenanceAdminAndVehicle();
    $m = Maintenance::create(['vehicle_id' => $v->id, 'km_alert_threshold' => 10000, 'date_dernier_entretien' => null]);

    $response = $this->actingAs($user)->delete(route('admin.maintenances.destroy', $m));

    $response->assertRedirect();
    $this->assertDatabaseMissing('maintenances', ['id' => $m->id]);
});
