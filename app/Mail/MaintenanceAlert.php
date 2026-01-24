<?php

namespace App\Mail;

use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MaintenanceAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Vehicle $vehicle,
        public Maintenance $maintenance
    ) {}

    public function build()
    {
        return $this->subject('Alerte entretien – ' . $this->vehicle->modele . ' (' . $this->vehicle->immatriculation . ')')
            ->markdown('emails.maintenance.alert');
    }
}
