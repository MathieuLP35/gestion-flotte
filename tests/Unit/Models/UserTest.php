<?php

use App\Models\Agence;
use App\Models\Passenger;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;

it('calculates total co2 saved for driver and passenger', function (): void {
    $agence = Agence::factory()->create();
    $driver = User::factory()->create(['agence_id' => $agence->id]);
    $passengerUser = User::factory()->create(['agence_id' => $agence->id]);

    $vehicle = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Zoé', 'immatriculation' => 'EL-1',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'electrique', 'en_maintenance' => false,
    ]);

    // Lyon -> Paris (approx 393 km)
    $reservation = Reservation::create([
        'user_id' => $driver->id,
        'vehicle_id' => $vehicle->id,
        'depart' => 'Lyon',
        'destination' => 'Paris',
        'depart_latitude' => 45.75,
        'depart_longitude' => 4.85,
        'destination_latitude' => 48.85,
        'destination_longitude' => 2.35,
        'date_debut' => now()->subDay(),
        'date_fin' => now(),
        'statut' => 'terminé',
        'covoiturage' => true,
    ]);

    Passenger::create([
        'reservation_id' => $reservation->id,
        'user_id' => $passengerUser->id,
        'statut' => 'confirme',
    ]);

    $driver->load(['reservationsAsDriver.vehicle', 'reservationsAsDriver.passengers']);
    $passengerUser->load(['reservationsAsPassenger.reservation.vehicle', 'reservationsAsPassenger.reservation.passengers']);

    $distance = $reservation->distance_km;

    // Driver gets total CO2 saved of the reservation
    // Baseline = 2 people * $distance * 130 = 260 * $distance
    // Actual = 0 (electric)
    // Saved = 260 * $distance / 1000
    $expectedDriverSaved = round((260 * $distance) / 1000, 1);

    // Passenger gets individual savings: 130 * distance - (actual / 2) = 130 * distance - 0
    $expectedPassengerSaved = round((130 * $distance) / 1000, 1);

    expect($driver->total_co2_saved)->toBe($expectedDriverSaved);
    expect($passengerUser->total_co2_saved)->toBe($expectedPassengerSaved);
});

it('calculates carpools count properly', function (): void {
    $agence = Agence::factory()->create();
    $driver = User::factory()->create(['agence_id' => $agence->id]);
    $passengerUser = User::factory()->create(['agence_id' => $agence->id]);

    $vehicle = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Zoé', 'immatriculation' => 'EL-1',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'electrique', 'en_maintenance' => false,
    ]);

    $reservation = Reservation::create([
        'user_id' => $driver->id,
        'vehicle_id' => $vehicle->id,
        'depart' => 'A',
        'destination' => 'B',
        'date_debut' => now()->subDay(),
        'date_fin' => now(),
        'statut' => 'terminé',
        'covoiturage' => true,
    ]);

    Passenger::create([
        'reservation_id' => $reservation->id,
        'user_id' => $passengerUser->id,
        'statut' => 'confirme',
    ]);

    $driver->load(['reservationsAsDriver.passengers']);
    $passengerUser->load(['reservationsAsPassenger.reservation']);

    expect($driver->carpools_count)->toBe(1);
    expect($passengerUser->carpools_count)->toBe(1);
});
