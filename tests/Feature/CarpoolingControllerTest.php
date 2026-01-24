<?php

use App\Models\Agence;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;

function userWithCovoiturage(): array
{
    $agence = Agence::factory()->create();
    $user = User::factory()->create(['agence_id' => $agence->id, 'email_verified_at' => now()]);
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Clio', 'immatriculation' => 'AB-COV',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);
    $r = Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $user->id, 'depart' => 'Lyon', 'destination' => 'Paris',
        'date_debut' => now()->addDay()->setTime(8, 0), 'date_fin' => now()->addDay()->setTime(12, 0),
        'statut' => 'validé', 'covoiturage' => true,
    ]);
    return [$user, $r];
}

it('recherche des covoiturages', function () {
    [$user, $r] = userWithCovoiturage();

    $response = $this->actingAs($user)->post(route('carpooling.search'), [
        'departure' => 'Lyon',
        'destination' => 'Paris',
        'departureDate' => now()->addDay()->toDateString(),
        'arrivalDate' => now()->addDay()->toDateString(),
    ]);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Carpooling/Search')->has('carpoolings'));
});
