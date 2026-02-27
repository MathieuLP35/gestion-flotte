<?php

use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Agence;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationStatusChanged;

beforeEach(function (): void {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'reservations.validate']);
    $role->givePermissionTo('reservations.validate');
});

it('can validate a reservation', function () {
    Mail::fake();

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $user = User::factory()->create();
    $vehicle = Vehicle::create([
        'agence_id' => Agence::factory()->create()->id,
        'modele' => 'Test',
        'immatriculation' => 'AB-123',
        'km_initial' => 0,
        'emplacement' => 'X',
        'nbr_places' => 5,
        'energie' => 'essence',
        'en_maintenance' => false,
    ]);

    $reservation = Reservation::create([
        'user_id' => $user->id,
        'vehicle_id' => $vehicle->id,
        'statut' => 'en attente',
        'depart' => 'A',
        'depart_lat' => 0,
        'depart_lng' => 0,
        'destination' => 'B',
        'destination_lat' => 1,
        'destination_lng' => 1,
        'date_debut' => now()->addDay(),
        'date_fin' => now()->addDays(2),
        'covoiturage' => false,
    ]);

    $response = $this->actingAs($admin)->put(route('admin.reservations.updateStatus', $reservation), [
        'statut' => 'validé',
    ]);

    $response->assertRedirect();
    expect($reservation->fresh()->statut)->toBe('validé');

    Mail::assertQueued(ReservationStatusChanged::class , function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        }
        );
    });