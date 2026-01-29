<?php

namespace App\Mail;

use App\Models\Passenger; // 1. Assurez-vous d'importer le modèle Passenger
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // 2. N'oubliez pas l'import pour la Queue
use Illuminate\Mail\Mailable; // <-- 3. L'IMPORT LE PLUS IMPORTANT (pour corriger l'erreur)
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// 4. Assurez-vous que votre classe "extends Mailable" ET "implements ShouldQueue"
class PassengerStatusUpdated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Passenger $passenger;

    /**
     * Create a new message instance.
     */
    public function __construct(Passenger $passenger)
    {
        $this->passenger = $passenger;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // On adapte le sujet en fonction du statut
        $subject = $this->passenger->statut === 'confirme'
            ? 'Votre demande de covoiturage a été confirmée !'
            : 'Mise à jour de votre demande de covoiturage';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // On pointe vers une vue Markdown (recommandé)
        return new Content(
            markdown: 'emails.passengers.status_updated',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
