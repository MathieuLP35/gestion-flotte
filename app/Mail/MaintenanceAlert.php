<?php

namespace App\Mail;

use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MaintenanceAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $vehicle;

    public function __construct(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
    }

    public function build()
    {
        return $this->subject("Alerte entretien pour véhicule " . $this->vehicle->immatriculation)
            ->markdown('emails.maintenance.alert');
    }
}
