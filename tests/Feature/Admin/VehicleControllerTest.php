<?php

use App\Models\Agence;
use App\Models\User;
use App\Models\Vehicle;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    foreach (['vehicles.view', 'vehicles.create', 'vehicles.edit', 'vehicles.delete'] as $name) {
        Permission::firstOrCreate(['name' => $name]);
    }
    $role->givePermissionTo(['vehicles.view', 'vehicles.create', 'vehicles.edit', 'vehicles.delete']);
});

function adminWithAgence(): array
{
    $agence = Agence::factory()->create();
    $user = User::factory()->create(['agence_id' => $agence->id]);
    $user->assignRole('Super Admin');

    return [$user, $agence];
}

it('redirige l\'index véhicules vers la page disponibilités', function (): void {
    [$user] = adminWithAgence();

    $response = $this->actingAs($user)->get(route('admin.vehicles.index'));

    $response->assertRedirect(route('admin.vehicles.availability'));
});

it('affiche le formulaire de création', function (): void {
    [$user] = adminWithAgence();

    $response = $this->actingAs($user)->get(route('admin.vehicles.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Admin/Vehicles/Create'));
});

it('affiche un véhicule', function (): void {
    [$user, $agence] = adminWithAgence();
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Clio', 'immatriculation' => 'AB-456',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $this->withoutVite();
    $response = $this->actingAs($user)->get(route('admin.vehicles.show', $v));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Admin/Vehicles/Show', false)->where('vehicle.id', $v->id));
});

it('refuse de voir un véhicule d\'une autre agence', function (): void {
    [$user] = adminWithAgence();
    $autre = Agence::factory()->create();
    $v = Vehicle::create([
        'agence_id' => $autre->id, 'modele' => 'Autre', 'immatriculation' => 'XX-999',
        'km_initial' => 0, 'emplacement' => 'Y', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $response = $this->actingAs($user)->get(route('admin.vehicles.show', $v));

    $response->assertForbidden();
});

it('crée un véhicule avec des clés', function (): void {
    [$user, $agence] = adminWithAgence();

    $response = $this->actingAs($user)->post(route('admin.vehicles.store'), [
        'modele' => 'Peugeot 208',
        'immatriculation' => 'AB-789-CD',
        'km_initial' => 0,
        'emplacement' => 'Garage A',
        'nbr_places' => 5,
        'nbr_cles' => 2,
        'energie' => 'essence',
    ]);

    $response->assertRedirect(route('admin.vehicles.availability'));
    $this->assertDatabaseHas('vehicles', ['immatriculation' => 'AB-789-CD', 'agence_id' => $agence->id]);
    expect(\App\Models\VehicleKey::where('vehicle_id', Vehicle::where('immatriculation', 'AB-789-CD')->first()->id)->count())->toBe(2);
});

it('affiche le formulaire d\'édition', function (): void {
    [$user, $agence] = adminWithAgence();
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Clio', 'immatriculation' => 'AB-111',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $response = $this->actingAs($user)->get(route('admin.vehicles.edit', $v));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Admin/Vehicles/Edit')->has('vehicle'));
});

it('met à jour un véhicule', function (): void {
    [$user, $agence] = adminWithAgence();
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Clio', 'immatriculation' => 'AB-222',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $response = $this->actingAs($user)->put(route('admin.vehicles.update', $v), [
        'modele' => 'Clio 5',
        'immatriculation' => 'AB-222',
        'km_initial' => 100,
        'emplacement' => 'Y',
        'nbr_places' => 5,
        'energie' => 'essence',
        'en_maintenance' => false,
    ]);

    $response->assertRedirect(route('admin.vehicles.availability'));
    $v->refresh();
    expect($v->modele)->toBe('Clio 5')->and($v->emplacement)->toBe('Y');
});

it('supprime un véhicule', function (): void {
    [$user, $agence] = adminWithAgence();
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Clio', 'immatriculation' => 'AB-333',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $response = $this->actingAs($user)->delete(route('admin.vehicles.destroy', $v));

    $response->assertRedirect(route('admin.vehicles.availability'));
    $this->assertDatabaseMissing('vehicles', ['id' => $v->id]);
});

it('affiche le calendrier d\'un véhicule', function (): void {
    [$user, $agence] = adminWithAgence();
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Clio', 'immatriculation' => 'AB-444',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $response = $this->actingAs($user)->get(route('admin.vehicles.calendar', $v));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Admin/Vehicles/Calendar')->has('vehicle')->has('reservations'));
});

it('affiche la page disponibilités', function (): void {
    [$user, $agence] = adminWithAgence();
    Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Clio', 'immatriculation' => 'AB-555',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $response = $this->actingAs($user)->get(route('admin.vehicles.availability'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Admin/Vehicles/Availability')->has('vehicles')->has('reservations'));
});

it('refuse edition d\'un véhicule d\'une autre agence', function (): void {
    [$user] = adminWithAgence();
    $autre = Agence::factory()->create();
    $v = Vehicle::create([
        'agence_id' => $autre->id, 'modele' => 'Autre', 'immatriculation' => 'ED-999',
        'km_initial' => 0, 'emplacement' => 'Y', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $response = $this->actingAs($user)->get(route('admin.vehicles.edit', $v));
    $response->assertForbidden();
});

it('refuse mise à jour d\'un véhicule d\'une autre agence', function (): void {
    [$user] = adminWithAgence();
    $autre = Agence::factory()->create();
    $v = Vehicle::create([
        'agence_id' => $autre->id, 'modele' => 'Autre', 'immatriculation' => 'UP-999',
        'km_initial' => 0, 'emplacement' => 'Y', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $response = $this->actingAs($user)->put(route('admin.vehicles.update', $v), [
        'modele' => 'O', 'immatriculation' => 'A', 'km_initial' => 0, 'emplacement' => 'Y', 'nbr_places' => 5, 'energie' => 'essence',
    ]);
    $response->assertForbidden();
});

it('refuse suppression d\'un véhicule d\'une autre agence', function (): void {
    [$user] = adminWithAgence();
    $autre = Agence::factory()->create();
    $v = Vehicle::create([
        'agence_id' => $autre->id, 'modele' => 'Autre', 'immatriculation' => 'DE-999',
        'km_initial' => 0, 'emplacement' => 'Y', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $response = $this->actingAs($user)->delete(route('admin.vehicles.destroy', $v));
    $response->assertForbidden();
});

it('refuse de voir le calendrier d\'un véhicule d\'une autre agence', function (): void {
    [$user] = adminWithAgence();
    $autre = Agence::factory()->create();
    $v = Vehicle::create([
        'agence_id' => $autre->id, 'modele' => 'Autre', 'immatriculation' => 'CA-999',
        'km_initial' => 0, 'emplacement' => 'Y', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $response = $this->actingAs($user)->get(route('admin.vehicles.calendar', $v));
    $response->assertForbidden();
});
