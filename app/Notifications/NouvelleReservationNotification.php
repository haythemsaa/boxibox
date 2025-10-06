<?php

namespace App\Notifications;

use App\Models\Contrat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NouvelleReservationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contrat;

    public function __construct(Contrat $contrat)
    {
        $this->contrat = $contrat;
    }

    /**
     * Déterminer les canaux de notification
     */
    public function via($notifiable)
    {
        $channels = ['database'];

        $settings = $notifiable->notificationSettings;

        if ($settings && $settings->isEnabled('nouvelle_reservation', 'email')) {
            $channels[] = 'mail';
        }

        if ($settings && $settings->isEnabled('nouvelle_reservation', 'push')) {
            // $channels[] = 'broadcast';
        }

        return $channels;
    }

    /**
     * Notification par email
     */
    public function toMail($notifiable)
    {
        $client = $this->contrat->client;
        $box = $this->contrat->box;

        return (new MailMessage)
            ->subject('Nouvelle réservation - ' . $this->contrat->numero_contrat)
            ->greeting('Bonjour,')
            ->line('Une nouvelle réservation vient d\'être effectuée.')
            ->line('Client : ' . $client->nom . ' ' . $client->prenom)
            ->line('Box : ' . ($box ? $box->numero : 'N/A'))
            ->line('Contrat : ' . $this->contrat->numero_contrat)
            ->line('Montant mensuel : ' . number_format($this->contrat->tarif_mensuel, 2) . ' €')
            ->action('Voir le contrat', url('/contrats/' . $this->contrat->id))
            ->line('Pensez à valider le contrat et à générer les codes d\'accès.');
    }

    /**
     * Notification en base de données
     */
    public function toArray($notifiable)
    {
        $client = $this->contrat->client;
        $box = $this->contrat->box;

        return [
            'type' => 'nouvelle_reservation',
            'title' => 'Nouvelle réservation',
            'message' => 'Réservation de ' . $client->nom . ' ' . $client->prenom . ' pour le box ' . ($box ? $box->numero : 'N/A'),
            'contrat_id' => $this->contrat->id,
            'numero_contrat' => $this->contrat->numero_contrat,
            'client_id' => $client->id,
            'client_nom' => $client->nom . ' ' . $client->prenom,
            'box_numero' => $box ? $box->numero : null,
            'icon' => 'fas fa-bell',
            'color' => 'info',
            'url' => '/contrats/' . $this->contrat->id,
        ];
    }
}
