<?php

use App\Models\Agence;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;

it('searchCarpoolings retourne les réservations covoiturage correspondantes', function (): void {
    $agence = Agence::factory()->create();
    $u = User::factory()->create(['agence_id' => $agence->id]);
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'C', 'immatriculation' => 'X1',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);
    Reservation::create([
        'vehicle_id' => $v->id, 'user_id' => $u->id, 'depart' => 'Lyon', 'destination' => 'Paris',
        'date_debut' => now()->addDay()->setTime(8, 0), 'date_fin' => now()->addDay()->setTime(12, 0),
        'statut' => 'validé', 'covoiturage' => true,
    ]);

    $r = Reservation::searchCarpoolings('Lyon', 'Paris', now()->addDay()->toDateString(), null);

    expect($r)->toHaveCount(1);
});

it('canBeReturned retourne true si statut autorisé et pas encore retourné', function (): void {
    $r = new Reservation(['statut' => 'validé', 'date_retour' => null]);
    expect($r->canBeReturned())->toBeTrue();
});

it('canBeReturned retourne false si date_retour renseignée', function (): void {
    $r = new Reservation(['statut' => 'validé', 'date_retour' => now()]);
    expect($r->canBeReturned())->toBeFalse();
});

it('isReturned retourne true si date_retour renseignée', function (): void {
    $r = new Reservation(['date_retour' => now()]);
    expect($r->isReturned())->toBeTrue();
});

it('isReturned retourne false si date_retour null', function (): void {
    $r = new Reservation(['date_retour' => null]);
    expect($r->isReturned())->toBeFalse();
});
