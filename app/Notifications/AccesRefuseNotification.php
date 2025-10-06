<?php

namespace App\Notifications;

use App\Models\AccessLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccesRefuseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $accessLog;

    public function __construct(AccessLog $accessLog)
    {
        $this->accessLog = $accessLog;
    }

    /**
     * Déterminer les canaux de notification
     */
    public function via($notifiable)
    {
        $channels = ['database'];

        $settings = $notifiable->notificationSettings;

        if ($settings && $settings->isEnabled('acces_refuse', 'email')) {
            $channels[] = 'mail';
        }

        if ($settings && $settings->isEnabled('acces_refuse', 'push')) {
            // $channels[] = 'broadcast';
        }

        if ($settings && $settings->isEnabled('acces_refuse', 'sms')) {
            // $channels[] = 'sms';
        }

        return $channels;
    }

    /**
     * Notification par email
     */
    public function toMail($notifiable)
    {
        $client = $this->accessLog->client;
        $box = $this->accessLog->box;

        return (new MailMessage)
            ->subject('⚠️ Tentative d\'accès refusée')
            ->greeting('Alerte sécurité')
            ->line('Une tentative d\'accès a été refusée.')
            ->line('Client : ' . ($client ? $client->nom . ' ' . $client->prenom : 'Inconnu'))
            ->line('Box : ' . ($box ? $box->numero : 'N/A'))
            ->line('Méthode : ' . strtoupper($this->accessLog->methode))
            ->line('Raison : ' . $this->accessLog->raison_refus)
            ->line('Date : ' . $this->accessLog->date_heure->format('d/m/Y H:i:s'))
            ->action('Voir les logs', url('/access-logs'))
            ->line('Vérifiez si cette tentative est légitime.');
    }

    /**
     * Notification en base de données
     */
    public function toArray($notifiable)
    {
        $client = $this->accessLog->client;
        $box = $this->accessLog->box;

        return [
            'type' => 'acces_refuse',
            'title' => 'Accès refusé',
            'message' => 'Tentative d\'accès refusée : ' . $this->accessLog->raison_refus,
            'access_log_id' => $this->accessLog->id,
            'client_id' => $client ? $client->id : null,
            'client_nom' => $client ? $client->nom . ' ' . $client->prenom : 'Inconnu',
            'box_id' => $box ? $box->id : null,
            'box_numero' => $box ? $box->numero : null,
            'methode' => $this->accessLog->methode,
            'raison' => $this->accessLog->raison_refus,
            'icon' => 'fas fa-lock',
            'color' => 'danger',
            'url' => '/access-logs',
        ];
    }
}
