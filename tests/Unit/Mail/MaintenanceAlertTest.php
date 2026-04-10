<?php

use App\Mail\MaintenanceAlert;
use App\Models\Agence;
use App\Models\Vehicle;

it('construit le mail MaintenanceAlert', function (): void {
    $agence = Agence::factory()->create();
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'C', 'immatriculation' => 'AB-ALT',
        'kilometrage' => 20000, 'last_service_km' => 0, 'service_interval_km' => 20000,
        'km_initial' => 20000, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $mail = new MaintenanceAlert($v);
    $built = $mail->build();
    expect($built->subject)->toContain('AB-ALT');
});
