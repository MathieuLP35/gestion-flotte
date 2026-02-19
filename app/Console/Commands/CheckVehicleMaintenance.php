<?php

namespace App\Console\Commands;

use App\Mail\MaintenanceAlert;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckVehicleMaintenance extends Command
{
    protected $signature = 'check:maintenance';

    protected $description = 'Vérifie si un véhicule nécessite un entretien';

    public function handle(): void
    {
        $vehicles = Vehicle::with('maintenances')->get();

        foreach ($vehicles as $vehicle) {
            foreach ($vehicle->maintenances as $maintenance) {
                if ($vehicle->km_initial >= $maintenance->km_alert_threshold) {
                    Mail::to(config('app.admin_email'))->send(new MaintenanceAlert($vehicle, $maintenance));
                }
            }
        }

        $this->info('Vérification des entretiens terminée.');
    }
}
