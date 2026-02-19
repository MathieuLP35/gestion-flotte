<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationStatusChanged extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation->loadMissing(['vehicle', 'driver']);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = match ($this->reservation->statut) {
            'en attente' => 'Réservation enregistrée',
            'validé' => 'Votre réservation est validée',
            'annulé' => 'Votre réservation a été annulée',
            'en cours' => 'Votre trajet a démarré',
            'à retourner' => 'Pensez à retourner le véhicule',
            'terminé' => 'Véhicule retourné – réservation terminée',
            default => 'Mise à jour de votre réservation',
        };

        return $this->subject($subject)
            ->markdown('emails.reservation.status_changed');
    }
}
