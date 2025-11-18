<?php

namespace App\Mail;

use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Maintenance; 

class MaintenanceAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $vehicle;
    public $maintenance;

    public function __construct(Vehicle $vehicle, Maintenance $maintenance)
    {
        $this->vehicle = $vehicle;
        $this->maintenance = $maintenance;
    }

    public function build()
    {
        return $this->subject("Alerte entretien pour un véhicule " . $this->vehicle->immatriculation)
            ->markdown('emails.maintenance.alert');
    }
}
