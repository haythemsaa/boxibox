<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'email_paiement_recu',
        'email_paiement_retard',
        'email_nouvelle_reservation',
        'email_fin_contrat',
        'email_acces_refuse',
        'push_paiement_recu',
        'push_paiement_retard',
        'push_nouvelle_reservation',
        'push_fin_contrat',
        'push_acces_refuse',
        'sms_paiement_retard',
        'sms_fin_contrat',
        'sms_acces_refuse',
        'notifications_activees',
        'heure_debut_notifications',
        'heure_fin_notifications',
    ];

    protected $casts = [
        'email_paiement_recu' => 'boolean',
        'email_paiement_retard' => 'boolean',
        'email_nouvelle_reservation' => 'boolean',
        'email_fin_contrat' => 'boolean',
        'email_acces_refuse' => 'boolean',
        'push_paiement_recu' => 'boolean',
        'push_paiement_retard' => 'boolean',
        'push_nouvelle_reservation' => 'boolean',
        'push_fin_contrat' => 'boolean',
        'push_acces_refuse' => 'boolean',
        'sms_paiement_retard' => 'boolean',
        'sms_fin_contrat' => 'boolean',
        'sms_acces_refuse' => 'boolean',
        'notifications_activees' => 'boolean',
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vérifier si les notifications sont autorisées à cette heure
     */
    public function canSendNotificationNow()
    {
        if (!$this->notifications_activees) {
            return false;
        }

        $heureActuelle = now()->format('H:i:s');

        return $heureActuelle >= $this->heure_debut_notifications
            && $heureActuelle <= $this->heure_fin_notifications;
    }

    /**
     * Vérifier si un type de notification est activé
     */
    public function isEnabled($type, $channel = 'email')
    {
        $field = $channel . '_' . $type;

        if (!isset($this->{$field})) {
            return false;
        }

        return $this->{$field} && $this->canSendNotificationNow();
    }
}
