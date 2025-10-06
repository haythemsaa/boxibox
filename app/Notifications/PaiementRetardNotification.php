<?php

namespace App\Notifications;

use App\Models\Facture;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaiementRetardNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $facture;
    protected $joursRetard;

    public function __construct(Facture $facture, $joursRetard)
    {
        $this->facture = $facture;
        $this->joursRetard = $joursRetard;
    }

    /**
     * Déterminer les canaux de notification
     */
    public function via($notifiable)
    {
        $channels = ['database'];

        $settings = $notifiable->notificationSettings;

        if ($settings && $settings->isEnabled('paiement_retard', 'email')) {
            $channels[] = 'mail';
        }

        if ($settings && $settings->isEnabled('paiement_retard', 'push')) {
            // $channels[] = 'broadcast';
        }

        if ($settings && $settings->isEnabled('paiement_retard', 'sms')) {
            // $channels[] = 'sms';
        }

        return $channels;
    }

    /**
     * Notification par email
     */
    public function toMail($notifiable)
    {
        $urgence = $this->joursRetard > 30 ? 'URGENT : ' : '';

        return (new MailMessage)
            ->subject($urgence . 'Facture impayée - ' . $this->facture->numero_facture)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Nous constatons un retard de paiement sur votre facture.')
            ->line('Numéro de facture : ' . $this->facture->numero_facture)
            ->line('Montant dû : ' . number_format($this->facture->montant_total - $this->facture->montant_paye, 2) . ' €')
            ->line('Retard : ' . $this->joursRetard . ' jours')
            ->line('Date d\'échéance : ' . $this->facture->date_echeance->format('d/m/Y'))
            ->action('Payer maintenant', url('/factures/' . $this->facture->id . '/payer'))
            ->line('Pour toute question, n\'hésitez pas à nous contacter.');
    }

    /**
     * Notification en base de données
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'paiement_retard',
            'title' => 'Facture en retard',
            'message' => 'Facture ' . $this->facture->numero_facture . ' en retard de ' . $this->joursRetard . ' jours',
            'facture_id' => $this->facture->id,
            'numero_facture' => $this->facture->numero_facture,
            'montant_du' => $this->facture->montant_total - $this->facture->montant_paye,
            'jours_retard' => $this->joursRetard,
            'icon' => 'fas fa-exclamation-triangle',
            'color' => $this->joursRetard > 30 ? 'danger' : 'warning',
            'url' => '/factures/' . $this->facture->id,
        ];
    }
}
