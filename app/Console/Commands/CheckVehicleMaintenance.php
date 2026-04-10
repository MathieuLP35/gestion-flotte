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
        $vehicles = Vehicle::all();

        foreach ($vehicles as $vehicle) {
            if ($vehicle->maintenance_status === 'overdue' || $vehicle->maintenance_status === 'warning') {
                Mail::to(config('app.admin_email'))->queue(new MaintenanceAlert($vehicle));
            }
        }

        $this->info('Vérification des entretiens terminée.');
    }
}
