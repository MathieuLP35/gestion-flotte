<?php

use App\Models\Agence;
use App\Models\Passenger;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

function userWithAgence(): array
{
    $agence = Agence::factory()->create();
    $user = User::factory()->create(['agence_id' => $agence->id, 'email_verified_at' => now()]);
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Clio', 'immatriculation' => 'AB-RES',
        'km_initial' => 1000, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);
    return [$user, $agence, $v];
}

it('affiche la liste des réservations du conducteur', function () {
    [$user, , $v] = userWithAgence();
    Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $user->id, 'depart' => 'Lyon', 'destination' => 'Paris',
        'date_debut' => now()->addDay(), 'date_fin' => now()->addDays(2), 'statut' => 'en attente', 'covoiturage' => false,
    ]);

    $this->withoutVite();
    $response = $this->actingAs($user)->get(route('reservations.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Reservations/Index', false)->has('reservations'));
});

it('affiche le tableau de bord avec réservations conducteur et passager', function () {
    [$user, , $v] = userWithAgence();
    $r = Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $user->id, 'depart' => 'Lyon', 'destination' => 'Paris',
        'date_debut' => now()->addDay(), 'date_fin' => now()->addDays(2), 'statut' => 'validé', 'covoiturage' => false,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('reservationsAsDriver')
        ->has('reservationsAsPassenger')
        ->has('geocoding')
    );
});

it('affiche le formulaire de création de réservation', function () {
    [$user, , $v] = userWithAgence();

    $response = $this->actingAs($user)->get(route('reservations.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Reservations/Create')->has('vehicles'));
});

it('suggère un véhicule', function () {
    [$user, $agence, $v] = userWithAgence();

    $response = $this->actingAs($user)->get(route('reservations.suggestVehicle') . '?' . http_build_query([
        'depart_lat' => 45.75,
        'depart_lng' => 4.85,
        'dest_lat' => 48.85,
        'dest_lng' => 2.35,
        'date_debut' => now()->addDay()->toDateTimeString(),
        'date_fin' => now()->addDays(2)->toDateTimeString(),
    ]));

    $response->assertOk();
    $response->assertJsonStructure(['suggestedVehicle', 'distance']);
});

it('crée une réservation', function () {
    [$user, , $v] = userWithAgence();
    $debut = now()->addDays(2)->setTime(10, 0);
    $fin = now()->addDays(2)->setTime(18, 0);

    $response = $this->actingAs($user)->post(route('reservations.store'), [
        'vehicle_id' => $v->id,
        'departure' => 'Lyon',
        'destination' => 'Paris',
        'date_debut' => $debut->toDateTimeString(),
        'date_fin' => $fin->toDateTimeString(),
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('reservations', ['user_id' => $user->id, 'vehicle_id' => $v->id]);
});

it('affiche une réservation', function () {
    [$user, , $v] = userWithAgence();
    $r = Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $user->id, 'depart' => 'Lyon', 'destination' => 'Paris',
        'date_debut' => now()->addDay(), 'date_fin' => now()->addDays(2), 'statut' => 'validé', 'covoiturage' => false,
    ]);

    $response = $this->actingAs($user)->get(route('reservations.show', $r));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Reservations/Show')->where('reservation.id', $r->id));
});

it('affiche le formulaire d\'édition', function () {
    [$user, , $v] = userWithAgence();
    $r = Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $user->id, 'depart' => 'Lyon', 'destination' => 'Paris',
        'date_debut' => now()->addDay(), 'date_fin' => now()->addDays(2), 'statut' => 'validé', 'covoiturage' => false,
    ]);

    $response = $this->actingAs($user)->get(route('reservations.edit', $r));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Reservations/Edit')->has('reservation')->has('vehicles'));
});

it('met à jour une réservation', function () {
    [$user, , $v] = userWithAgence();
    $debut = now()->addDays(3)->setTime(10, 0);
    $fin = now()->addDays(3)->setTime(18, 0);
    $r = Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $user->id, 'depart' => 'Lyon', 'destination' => 'Paris',
        'date_debut' => now()->addDay(), 'date_fin' => now()->addDays(2), 'statut' => 'validé', 'covoiturage' => false,
    ]);

    $response = $this->actingAs($user)->put(route('reservations.update', $r), [
        'vehicle_id' => $v->id,
        'departure' => 'Lyon',
        'destination' => 'Lille',
        'date_debut' => $debut->toDateTimeString(),
        'date_fin' => $fin->toDateTimeString(),
        'statut' => 'validé',
    ]);

    $response->assertRedirect(route('reservations.index'));
});

it('supprime une réservation', function () {
    [$user, , $v] = userWithAgence();
    $r = Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $user->id, 'depart' => 'Lyon', 'destination' => 'Paris',
        'date_debut' => now()->addDay(), 'date_fin' => now()->addDays(2), 'statut' => 'annulé', 'covoiturage' => false,
    ]);

    $response = $this->actingAs($user)->delete(route('reservations.destroy', $r));

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseMissing('reservations', ['id' => $r->id]);
});

it('vérifie les covoiturages disponibles', function () {
    [$user, , $v] = userWithAgence();
    $autre = User::factory()->create(['agence_id' => $v->agence_id, 'email_verified_at' => now()]);
    Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $autre->id, 'depart' => 'lyon', 'destination' => 'paris',
        'date_debut' => now()->addDay()->setTime(8, 0), 'date_fin' => now()->addDay()->setTime(12, 0),
        'statut' => 'validé', 'covoiturage' => true,
    ]);

    $response = $this->actingAs($user)->post(route('reservations.checkCarpool'), [
        'date_debut' => now()->addDay()->toDateTimeString(),
        'departure' => 'Lyon',
        'destination' => 'Paris',
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['carpool_available', 'reservations']);
});

it('affiche le formulaire de retour', function () {
    [$user, , $v] = userWithAgence();
    $r = Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $user->id, 'depart' => 'Lyon', 'destination' => 'Paris',
        'date_debut' => now()->subHour(), 'date_fin' => now()->addHours(2), 'statut' => 'validé', 'covoiturage' => false,
    ]);

    $response = $this->actingAs($user)->get(route('reservations.return.form', $r));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Reservations/Return')->has('reservation'));
});

it('traite le retour du véhicule', function () {
    [$user, , $v] = userWithAgence();
    $r = Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $user->id, 'depart' => 'Lyon', 'destination' => 'Paris',
        'date_debut' => now()->subDay(), 'date_fin' => now()->addHour(), 'statut' => 'à retourner', 'covoiturage' => false,
    ]);

    $response = $this->actingAs($user)->post(route('reservations.return', $r), [
        'km_final' => 1500,
        'emplacement_retour' => 'Garage',
        'etat_vehicule' => 'bon',
        'notes_retour' => null,
    ]);

    $response->assertRedirect(route('reservations.show', $r));
    $r->refresh();
    expect($r->statut)->toBe('terminé')->and($r->date_retour)->not->toBeNull();
});

it('lance le trajet', function () {
    [$user, , $v] = userWithAgence();
    $r = Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $user->id, 'depart' => 'Lyon', 'destination' => 'Paris',
        'date_debut' => now()->addHour(), 'date_fin' => now()->addHours(3), 'statut' => 'validé', 'covoiturage' => false,
    ]);

    $response = $this->actingAs($user)->post(route('reservations.start', $r));

    $response->assertRedirect();
    $r->refresh();
    expect($r->statut)->toBe('en cours');
});

it('termine le trajet', function () {
    [$user, , $v] = userWithAgence();
    $r = Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $user->id, 'depart' => 'Lyon', 'destination' => 'Paris',
        'date_debut' => now()->subHour(), 'date_fin' => now()->addHours(2), 'statut' => 'en cours', 'covoiturage' => false,
    ]);

    $response = $this->actingAs($user)->post(route('reservations.end', $r));

    $response->assertRedirect();
    $r->refresh();
    expect($r->statut)->toBe('à retourner');
});
