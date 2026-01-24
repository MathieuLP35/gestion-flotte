<?php

use App\Events\MessageSent;
use App\Models\Agence;
use App\Models\Message;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;

it('diffuse sur le canal privé de la réservation', function () {
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
    $m = Message::create(['reservation_id' => $r->id, 'user_id' => $u->id, 'body' => 'Test']);

    $event = new MessageSent($m);
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1);
    expect($channels[0]->name)->toContain('reservation.' . $r->id);
});
