<?php

use App\Mail\MaintenanceAlert;
use App\Models\Agence;
use App\Models\Maintenance;
use App\Models\Vehicle;

it('construit le mail MaintenanceAlert', function () {
    $agence = Agence::factory()->create();
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'C', 'immatriculation' => 'AB-ALT',
        'km_initial' => 20000, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);
    $m = Maintenance::create(['vehicle_id' => $v->id, 'km_alert_threshold' => 15000, 'date_dernier_entretien' => '2024-01-01']);

    $mail = new MaintenanceAlert($v, $m);
    $built = $mail->build();
    expect($built->subject)->toContain('AB-ALT');
});
