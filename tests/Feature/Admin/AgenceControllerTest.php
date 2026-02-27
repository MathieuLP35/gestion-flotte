<?php

use App\Models\Agence;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'agences.view']);
    Permission::firstOrCreate(['name' => 'agences.view_all']);
    $role->givePermissionTo(['agences.view']);
});

it('can fetch all agences if user has agences.view_all permission', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');
    $admin->givePermissionTo('agences.view_all');

    Agence::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get(route('admin.agences.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->component('Admin/Agences/Index'));
});

it('can only fetch user agency if user does not have view_all permission', function () {
    $agence = Agence::factory()->create();
    $autreAgence = Agence::factory()->create();

    $admin = User::factory()->create(['agence_id' => $agence->id]);
    $admin->assignRole('Super Admin');

    $response = $this->actingAs($admin)->get(route('admin.agences.index'));

    $response->assertOk();
});

it('can view create agency page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $response = $this->actingAs($admin)->get(route('admin.agences.create'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->component('Admin/Agences/Create'));
});

it('can store a new agency', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $response = $this->actingAs($admin)->post(route('admin.agences.store'), [
        'nom' => 'Nouvelle Agence',
        'adresse' => '123 Rue Test',
    ]);

    $response->assertRedirect(route('admin.agences.index'));
    $this->assertDatabaseHas('agences', ['nom' => 'Nouvelle Agence']);
});

it('can view edit page for user agency', function () {
    $agence = Agence::factory()->create();
    $admin = User::factory()->create(['agence_id' => $agence->id]);
    $admin->assignRole('Super Admin');

    $response = $this->actingAs($admin)->get(route('admin.agences.edit', $agence));

    $response->assertOk();
});

it('forbids editing an agency of another user without view_all permission', function () {
    $agence = Agence::factory()->create();
    $autreAgence = Agence::factory()->create();
    $admin = User::factory()->create(['agence_id' => $agence->id]);
    $admin->assignRole('Super Admin');

    $response = $this->actingAs($admin)->get(route('admin.agences.edit', $autreAgence));

    $response->assertForbidden();
});

it('can update user agency', function () {
    $agence = Agence::factory()->create(['nom' => 'Old Name']);
    $admin = User::factory()->create(['agence_id' => $agence->id]);
    $admin->assignRole('Super Admin');

    $response = $this->actingAs($admin)->put(route('admin.agences.update', $agence), [
        'nom' => 'New Name',
    ]);

    $response->assertRedirect(route('admin.agences.index'));
    expect($agence->fresh()->nom)->toBe('New Name');
});

it('forbids updating an agency of another user without view_all permission', function () {
    $agence = Agence::factory()->create();
    $autreAgence = Agence::factory()->create();
    $admin = User::factory()->create(['agence_id' => $agence->id]);
    $admin->assignRole('Super Admin');

    $response = $this->actingAs($admin)->put(route('admin.agences.update', $autreAgence), [
        'nom' => 'New Name',
    ]);

    $response->assertForbidden();
});

it('can destroy user agency if it is empty', function () {
    $agence = Agence::factory()->create();
    $admin = User::factory()->create(['agence_id' => $agence->id]);
    $admin->assignRole('Super Admin');

    // We must dissociate the user since the controller checks if the agency has users
    $admin->update(['agence_id' => null]);

    // But wait, the controller uses $admin->agence_id to authorize!
    // We'll give the user view_all to allow destruction of an empty agency not their own
    $admin->givePermissionTo('agences.view_all');

    $response = $this->actingAs($admin)->delete(route('admin.agences.destroy', $agence));

    $response->assertRedirect(route('admin.agences.index'));
    $this->assertDatabaseMissing('agences', ['id' => $agence->id]);
});

it('forbids destroying an agency of another user without view_all permission', function () {
    $agence = Agence::factory()->create();
    $autreAgence = Agence::factory()->create();
    $admin = User::factory()->create(['agence_id' => $agence->id]);
    $admin->assignRole('Super Admin');

    $response = $this->actingAs($admin)->delete(route('admin.agences.destroy', $autreAgence));

    $response->assertForbidden();
});

it('cannot destroy an agency with users', function () {
    $agence = Agence::factory()->create();
    $admin = User::factory()->create(['agence_id' => $agence->id]);
    $admin->assignRole('Super Admin');

    $response = $this->actingAs($admin)->delete(route('admin.agences.destroy', $agence));

    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('agences', ['id' => $agence->id]);
});

it('cannot destroy an agency with vehicles', function () {
    $agence = Agence::factory()->create();
    $admin = User::factory()->create(['agence_id' => $agence->id]);
    $admin->assignRole('Super Admin');

    $admin->update(['agence_id' => null]);
    $admin->givePermissionTo('agences.view_all');

    \App\Models\Vehicle::create([
        'agence_id' => $agence->id,
        'modele' => 'Test',
        'immatriculation' => 'AB-123',
        'km_initial' => 0,
        'emplacement' => 'X',
        'nbr_places' => 5,
        'energie' => 'essence',
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.agences.destroy', $agence));

    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('agences', ['id' => $agence->id]);
});
