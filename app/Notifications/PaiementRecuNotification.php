<?php

namespace App\Notifications;

use App\Models\Reglement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaiementRecuNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reglement;

    public function __construct(Reglement $reglement)
    {
        $this->reglement = $reglement;
    }

    /**
     * Déterminer les canaux de notification
     */
    public function via($notifiable)
    {
        $channels = ['database'];

        $settings = $notifiable->notificationSettings;

        if ($settings && $settings->isEnabled('paiement_recu', 'email')) {
            $channels[] = 'mail';
        }

        // Si push notifications configurées
        if ($settings && $settings->isEnabled('paiement_recu', 'push')) {
            // $channels[] = 'broadcast';
        }

        return $channels;
    }

    /**
     * Notification par email
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Paiement reçu - ' . $this->reglement->numero_recu)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Nous avons bien reçu votre paiement.')
            ->line('Montant : ' . number_format($this->reglement->montant, 2) . ' €')
            ->line('Mode de paiement : ' . $this->reglement->mode_paiement)
            ->line('Numéro de reçu : ' . $this->reglement->numero_recu)
            ->action('Voir le détail', url('/reglements/' . $this->reglement->id))
            ->line('Merci pour votre confiance !');
    }

    /**
     * Notification en base de données
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'paiement_recu',
            'title' => 'Paiement reçu',
            'message' => 'Paiement de ' . number_format($this->reglement->montant, 2) . ' € reçu',
            'reglement_id' => $this->reglement->id,
            'numero_recu' => $this->reglement->numero_recu,
            'montant' => $this->reglement->montant,
            'icon' => 'fas fa-check-circle',
            'color' => 'success',
            'url' => '/reglements/' . $this->reglement->id,
        ];
    }
}
