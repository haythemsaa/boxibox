<?php

namespace App\Mail;

use App\Models\Facture;
use App\Models\Reglement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentNotificationAdmin extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $facture;
    public $reglement;
    public $client;

    /**
     * Create a new message instance.
     */
    public function __construct(Facture $facture, Reglement $reglement)
    {
        $this->facture = $facture->load('contrat', 'client');
        $this->reglement = $reglement;
        $this->client = $facture->client;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '💳 Nouveau paiement en ligne - ' . $this->facture->numero_facture,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-notification-admin',
            with: [
                'facture' => $this->facture,
                'reglement' => $this->reglement,
                'client' => $this->client,
                'montant' => number_format($this->reglement->montant, 2, ',', ' ') . ' €',
                'date_paiement' => $this->reglement->date_reglement->format('d/m/Y à H:i'),
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
