<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Mail;
use App\Mail\MaintenanceAlert;

class CheckVehicleMaintenance extends Command
{
    protected $signature = 'check:maintenance';

    protected $description = 'Vérifie si un véhicule nécessite un entretien';

    public function handle()
    {
        $vehicles = Vehicle::with('maintenances')->get();

        foreach ($vehicles as $vehicle) {
            foreach ($vehicle->maintenances as $maintenance) {
                if ($vehicle->km_initial >= $maintenance->km_alert_threshold) {
                    Mail::to(config('app.admin_email'))->send(new MaintenanceAlert($vehicle));
                }
            }
        }

        $this->info('Vérification des entretiens terminée.');
    }
}
