<?php

use App\Mail\PassengerStatusUpdated;
use App\Models\Agence;
use App\Models\Passenger;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;

it('construit le mail PassengerStatusUpdated avec sujet adapté au statut confirme', function (): void {
    $agence = Agence::factory()->create();
    $driver = User::factory()->create(['agence_id' => $agence->id]);
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'C', 'immatriculation' => 'X',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);
    $r = Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $driver->id, 'depart' => 'A', 'destination' => 'B',
        'date_debut' => now()->addDay(), 'date_fin' => now()->addDays(2), 'statut' => 'validé', 'covoiturage' => true,
    ]);
    $p = Passenger::create(['reservation_id' => $r->id, 'user_id' => $driver->id, 'statut' => 'confirme']);

    $mailable = new PassengerStatusUpdated($p);
    expect($mailable->envelope()->subject)->toContain('confirmée');
});

it('construit le mail PassengerStatusUpdated avec sujet pour refuse', function (): void {
    $agence = Agence::factory()->create();
    $driver = User::factory()->create(['agence_id' => $agence->id]);
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'C', 'immatriculation' => 'X',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);
    $r = Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $driver->id, 'depart' => 'A', 'destination' => 'B',
        'date_debut' => now()->addDay(), 'date_fin' => now()->addDays(2), 'statut' => 'validé', 'covoiturage' => true,
    ]);
    $p = Passenger::create(['reservation_id' => $r->id, 'user_id' => $driver->id, 'statut' => 'refuse']);

    $mailable = new PassengerStatusUpdated($p);
    expect($mailable->envelope()->subject)->toContain('Mise à jour');
});
