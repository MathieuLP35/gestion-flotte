<?php

use App\Models\Agence;
use App\Models\Passenger;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
});

function reservationWithDriver(): array
{
    $agence = Agence::factory()->create();
    $driver = User::factory()->create(['agence_id' => $agence->id, 'email_verified_at' => now()]);
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Clio', 'immatriculation' => 'AB-PAS',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);
    $r = Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $driver->id, 'depart' => 'Lyon', 'destination' => 'Paris',
        'date_debut' => now()->addDay(), 'date_fin' => now()->addDays(2), 'statut' => 'validé', 'covoiturage' => true,
    ]);

    return [$driver, $r, $agence];
}

it('ajoute une demande de passager', function (): void {
    [$driver, $r, $agence] = reservationWithDriver();
    $passager = User::factory()->create(['agence_id' => $agence->id, 'email_verified_at' => now()]);

    $response = $this->actingAs($passager)->post(route('passengers.store'), [
        'reservation_id' => $r->id,
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('passengers', ['reservation_id' => $r->id, 'user_id' => $passager->id, 'statut' => 'en_attente']);
});

it('refuse un doublon passager', function (): void {
    [$driver, $r, $agence] = reservationWithDriver();
    $passager = User::factory()->create(['agence_id' => $agence->id, 'email_verified_at' => now()]);
    Passenger::create(['reservation_id' => $r->id, 'user_id' => $passager->id, 'statut' => 'en_attente']);

    $response = $this->actingAs($passager)->post(route('passengers.store'), [
        'reservation_id' => $r->id,
    ]);

    $response->assertRedirect();
    expect(Passenger::where('reservation_id', $r->id)->where('user_id', $passager->id)->count())->toBe(1);
});

it('met à jour le statut d\'un passager', function (): void {
    [$driver, $r, $agence] = reservationWithDriver();
    $passager = User::factory()->create(['agence_id' => $agence->id, 'email_verified_at' => now()]);
    $p = Passenger::create(['reservation_id' => $r->id, 'user_id' => $passager->id, 'statut' => 'en_attente']);

    $response = $this->actingAs($driver)->put(route('passengers.update', $p), [
        'statut' => 'confirme',
    ]);

    $response->assertRedirect();
    $p->refresh();
    expect($p->statut)->toBe('confirme');
});

it('supprime un passager', function (): void {
    [$driver, $r, $agence] = reservationWithDriver();
    $passager = User::factory()->create(['agence_id' => $agence->id, 'email_verified_at' => now()]);
    $p = Passenger::create(['reservation_id' => $r->id, 'user_id' => $passager->id, 'statut' => 'en_attente']);

    $response = $this->actingAs($driver)->delete(route('passengers.destroy', $p));

    $response->assertRedirect();
    $this->assertDatabaseMissing('passengers', ['id' => $p->id]);
});
