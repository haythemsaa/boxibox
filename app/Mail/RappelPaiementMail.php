<?php

namespace App\Mail;

use App\Models\Rappel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RappelPaiementMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $rappel;

    /**
     * Create a new message instance.
     */
    public function __construct(Rappel $rappel)
    {
        $this->rappel = $rappel;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $niveau = $this->rappel->niveau;
        $subject = match($niveau) {
            1 => 'Rappel de paiement - BOXIBOX',
            2 => 'Relance 2ème niveau - Facture impayée',
            3 => 'URGENT - Mise en demeure de payer',
            default => 'Rappel de paiement'
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.rappel-paiement',
            with: [
                'rappel' => $this->rappel,
                'facture' => $this->rappel->facture,
                'client' => $this->rappel->facture->client,
            ],
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
