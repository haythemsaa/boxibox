<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'facture_id',
        'client_id',
        'type',
        'canal',
        'date_envoi',
        'email_sent',
        'sms_sent',
        'message',
    ];

    protected $casts = [
        'date_envoi' => 'date',
        'email_sent' => 'boolean',
        'sms_sent' => 'boolean',
    ];

    /**
     * Relation avec la facture
     */
    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    /**
     * Relation avec le client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
