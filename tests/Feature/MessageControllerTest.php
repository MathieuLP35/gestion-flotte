<?php

use App\Models\Agence;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;

function reservationForMessage(): array
{
    $agence = Agence::factory()->create();
    $user = User::factory()->create(['agence_id' => $agence->id, 'email_verified_at' => now()]);
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Clio', 'immatriculation' => 'AB-MSG',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);
    $r = Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $user->id, 'depart' => 'Lyon', 'destination' => 'Paris',
        'date_debut' => now()->addDay(), 'date_fin' => now()->addDays(2), 'statut' => 'validé', 'covoiturage' => false,
    ]);

    return [$user, $r];
}

it('récupère les messages d\'une réservation', function (): void {
    [$user, $r] = reservationForMessage();

    $response = $this->actingAs($user)->get(route('messages.index', $r));

    $response->assertOk();
    $data = $response->json();
    expect($data)->toBeArray();
});

it('envoie un message dans une réservation', function (): void {
    [$user, $r] = reservationForMessage();

    $response = $this->actingAs($user)->post(route('messages.store', $r), [
        'body' => 'Hello',
    ]);

    $response->assertStatus(201);
    $response->assertJsonFragment(['body' => 'Hello']);
    $this->assertDatabaseHas('messages', ['reservation_id' => $r->id, 'user_id' => $user->id, 'body' => 'Hello']);
});
