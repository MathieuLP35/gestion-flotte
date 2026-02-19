<?php

use App\Mail\ReservationStatusChanged;
use App\Models\Agence;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;

it('construit le mail ReservationStatusChanged', function (): void {
    $agence = Agence::factory()->create();
    $u = User::factory()->create(['agence_id' => $agence->id]);
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'C', 'immatriculation' => 'X',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);
    $r = Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $u->id, 'depart' => 'A', 'destination' => 'B',
        'date_debut' => now()->addDay(), 'date_fin' => now()->addDays(2), 'statut' => 'validé', 'covoiturage' => false,
    ]);

    $mailable = new ReservationStatusChanged($r);
    expect($mailable->build())->toBe($mailable);
});
