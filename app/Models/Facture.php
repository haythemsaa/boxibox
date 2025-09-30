<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'contrat_id',
        'numero_facture',
        'type_facture',
        'date_emission',
        'date_echeance',
        'montant_ht',
        'taux_tva',
        'montant_tva',
        'montant_ttc',
        'statut',
        'notes',
        'date_paiement',
        'mode_paiement',
        'reference_paiement',
    ];

    protected $casts = [
        'date_emission' => 'date',
        'date_echeance' => 'date',
        'date_paiement' => 'date',
        'montant_ht' => 'decimal:2',
        'taux_tva' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    public function lignes()
    {
        return $this->hasMany(FactureLigne::class);
    }

    public function reglements()
    {
        return $this->hasMany(Reglement::class);
    }

    public function relances()
    {
        return $this->hasMany(Relance::class);
    }

    public function reminders()
    {
        return $this->hasMany(PaymentReminder::class);
    }

    public function scopePayee($query)
    {
        return $query->where('statut', 'payee');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeEnRetard($query)
    {
        return $query->where('statut', 'en_retard');
    }

    public function isPayee()
    {
        return $this->statut === 'payee';
    }

    public function isEnRetard()
    {
        return $this->date_echeance->isPast() && !$this->isPayee();
    }

    public function getMontantRestantAttribute()
    {
        $montantRegle = $this->reglements()->sum('montant');
        return $this->montant_ttc - $montantRegle;
    }
}