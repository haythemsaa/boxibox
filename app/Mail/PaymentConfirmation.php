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

class PaymentConfirmation extends Mailable implements ShouldQueue
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
        $this->facture = $facture->load('contrat');
        $this->reglement = $reglement;
        $this->client = $facture->client;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de paiement - Facture ' . $this->facture->numero_facture,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-confirmation',
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
