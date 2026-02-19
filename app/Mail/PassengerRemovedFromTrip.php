<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PassengerRemovedFromTrip extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
        public string $removedUserName
    ) {
        $this->reservation->loadMissing(['vehicle', 'driver']);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Vous avez été retiré d’un trajet covoiturage')
            ->markdown('emails.passengers.removed_from_trip');
    }
}
