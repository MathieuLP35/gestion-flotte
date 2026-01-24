<?php

namespace App\Mail;

use App\Models\Passenger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PassengerRequestReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Passenger $passenger
    ) {
        $this->passenger->loadMissing(['user', 'reservation.vehicle', 'reservation.driver']);
    }

    public function build()
    {
        return $this->subject('Nouvelle demande de covoiturage pour votre trajet')
            ->markdown('emails.passengers.request_received');
    }
}
