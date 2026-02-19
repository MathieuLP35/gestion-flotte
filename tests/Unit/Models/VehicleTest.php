<?php

use App\Models\Vehicle;

it('calculateDistance calcule la distance Haversine', function (): void {
    // Lyon (45.75, 4.85) -> Paris (48.85, 2.35) ~ 393 km
    $d = Vehicle::calculateDistance(45.75, 4.85, 48.85, 2.35);
    expect($d)->toBeGreaterThan(350)->and($d)->toBeLessThan(500);
});

it('suggestBestVehicle retourne un véhicule électrique pour petit trajet', function (): void {
    $agence = \App\Models\Agence::factory()->create();
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Zoé', 'immatriculation' => 'EL-1',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'electrique', 'en_maintenance' => false,
    ]);
    Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'Clio', 'immatriculation' => 'ES-1',
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $s = Vehicle::suggestBestVehicle($agence->id, 50, null, null);

    expect($s)->not->toBeNull()->and($s->energie)->toBe('electrique');
});

it('suggestBestVehicle retourne null si aucun véhicule', function (): void {
    $s = Vehicle::suggestBestVehicle(99999, 50, null, null);
    expect($s)->toBeNull();
});
