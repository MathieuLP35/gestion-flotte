<?php

use App\Models\Agence;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
});

it('affiche le tableau de bord admin avec les stats', function (): void {
    $agence = Agence::factory()->create();
    $user = User::factory()->create(['agence_id' => $agence->id]);
    $user->assignRole('Super Admin');

    Vehicle::create([
        'agence_id' => $agence->id,
        'modele' => 'Clio',
        'immatriculation' => 'AB-123',
        'km_initial' => 0,
        'emplacement' => 'X',
        'nbr_places' => 5,
        'energie' => 'essence',
        'en_maintenance' => false,
    ]);

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Dashboard')
        ->has('stats')
        ->where('stats.users_count', User::count())
        ->where('stats.vehicles_count', Vehicle::count())
        ->where('stats.reservations_count', Reservation::count())
    );
});

it('refuse l\'accès au tableau de bord admin sans rôle Super Admin', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertForbidden();
});
