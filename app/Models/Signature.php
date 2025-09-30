<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Signature extends Model
{
    use HasFactory;

    protected $fillable = [
        'signable_type',
        'signable_id',
        'client_id',
        'signataire_nom',
        'signataire_email',
        'signataire_telephone',
        'statut',
        'signature_data',
        'signature_ip',
        'signature_method',
        'date_envoi',
        'date_signature',
        'date_expiration',
        'token',
        'certificat_path',
        'notes',
    ];

    protected $casts = [
        'date_envoi' => 'datetime',
        'date_signature' => 'datetime',
        'date_expiration' => 'datetime',
    ];

    /**
     * Relation polymorphique vers le document à signer (Contrat ou MandatSepa)
     */
    public function signable()
    {
        return $this->morphTo();
    }

    /**
     * Relation vers le client signataire
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Génère un token unique pour le lien de signature
     */
    public static function generateToken()
    {
        return Str::random(64);
    }

    /**
     * Vérifie si la signature est expirée
     */
    public function isExpired()
    {
        return $this->date_expiration && now()->isAfter($this->date_expiration);
    }

    /**
     * Vérifie si la signature est en attente
     */
    public function isPending()
    {
        return $this->statut === 'en_attente' && !$this->isExpired();
    }

    /**
     * Vérifie si le document est signé
     */
    public function isSigned()
    {
        return $this->statut === 'signe';
    }

    /**
     * Marquer comme expiré si la date est dépassée
     */
    public function checkExpiration()
    {
        if ($this->isExpired() && $this->statut === 'en_attente') {
            $this->update(['statut' => 'expire']);
        }
    }

    /**
     * Scope pour les signatures en attente
     */
    public function scopePending($query)
    {
        return $query->where('statut', 'en_attente')
                    ->where(function($q) {
                        $q->whereNull('date_expiration')
                          ->orWhere('date_expiration', '>', now());
                    });
    }

    /**
     * Scope pour les signatures signées
     */
    public function scopeSigned($query)
    {
        return $query->where('statut', 'signe');
    }

    /**
     * Scope pour les signatures expirées
     */
    public function scopeExpired($query)
    {
        return $query->where('statut', 'expire')
                    ->orWhere(function($q) {
                        $q->where('statut', 'en_attente')
                          ->where('date_expiration', '<=', now());
                    });
    }
}
