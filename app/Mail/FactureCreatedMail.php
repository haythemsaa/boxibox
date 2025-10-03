<?php

namespace App\Mail;

use App\Models\Facture;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class FactureCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $facture;

    /**
     * Create a new message instance.
     */
    public function __construct(Facture $facture)
    {
        $this->facture = $facture;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle facture BOXIBOX - ' . $this->facture->numero_facture,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.facture-created',
            with: [
                'facture' => $this->facture,
                'client' => $this->facture->client,
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
        $client = $this->facture->client;
        $pdf = Pdf::loadView('pdf.facture', [
            'facture' => $this->facture,
            'client' => $client
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'facture_' . $this->facture->numero_facture . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
