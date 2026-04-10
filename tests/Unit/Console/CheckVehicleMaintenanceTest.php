<?php

use App\Models\Agence;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
    Config::set('app.admin_email', 'admin@test.com');
});

it('envoie un mail d\'alerte quand statuts warning ou overdue', function (): void {
    $agence = Agence::factory()->create();
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'C', 'immatriculation' => 'AB-CMD',
        'kilometrage' => 20000, 'last_service_km' => 0, 'service_interval_km' => 20000,
        'km_initial' => 0, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $this->artisan('check:maintenance')
        ->expectsOutput('Vérification des entretiens terminée.')
        ->assertSuccessful();

    Mail::assertQueued(\App\Mail\MaintenanceAlert::class);
});

it('n\'envoie pas de mail si statut est OK', function (): void {
    $agence = Agence::factory()->create();
    $v = Vehicle::create([
        'agence_id' => $agence->id, 'modele' => 'C', 'immatriculation' => 'AB-CMD2',
        'kilometrage' => 5000, 'last_service_km' => 0, 'service_interval_km' => 20000,
        'km_initial' => 5000, 'emplacement' => 'X', 'nbr_places' => 5, 'energie' => 'essence', 'en_maintenance' => false,
    ]);

    $this->artisan('check:maintenance')->assertSuccessful();

    Mail::assertNotSent(\App\Mail\MaintenanceAlert::class);
});
