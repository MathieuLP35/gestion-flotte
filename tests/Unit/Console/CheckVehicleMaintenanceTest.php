<?php

use App\Models\Agence;
use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    Config::set('app.admin_email', 'admin@test.com');
});

it('envoie un mail d\'alerte quand km_initial >= km_alert_threshold', function () {
    $agence = Agence::factory()->create();
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'C', 'immatriculation' => 'AB-CMD',
        'km_initial' => 20000, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);
    Maintenance::create(['vehicle_id' => $v->id, 'km_alert_threshold' => 15000, 'date_dernier_entretien' => null]);

    $this->artisan('check:maintenance')
        ->expectsOutput('Vérification des entretiens terminée.')
        ->assertSuccessful();

    Mail::assertQueued(\App\Mail\MaintenanceAlert::class);
});

it('n\'envoie pas de mail si km_initial < km_alert_threshold', function () {
    $agence = Agence::factory()->create();
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'C', 'immatriculation' => 'AB-CMD2',
        'km_initial' => 5000, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);
    Maintenance::create(['vehicle_id' => $v->id, 'km_alert_threshold' => 15000, 'date_dernier_entretien' => null]);

    $this->artisan('check:maintenance')->assertSuccessful();

    Mail::assertNotSent(\App\Mail\MaintenanceAlert::class);
});
