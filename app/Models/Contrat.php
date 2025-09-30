<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contrat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'box_id',
        'numero_contrat',
        'date_debut',
        'date_fin',
        'duree_type',
        'prix_mensuel',
        'caution',
        'frais_dossier',
        'periodicite_facturation',
        'statut',
        'notes',
        'date_signature',
        'renouvellement_automatique',
        'preavis_jours',
        'assurance_incluse',
        'montant_assurance',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_signature' => 'date',
        'prix_mensuel' => 'decimal:2',
        'caution' => 'decimal:2',
        'frais_dossier' => 'decimal:2',
        'montant_assurance' => 'decimal:2',
        'renouvellement_automatique' => 'boolean',
        'assurance_incluse' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function box()
    {
        return $this->belongsTo(Box::class);
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    public function services()
    {
        return $this->hasMany(ContratService::class);
    }

    public function signatures()
    {
        return $this->morphMany(Signature::class, 'signable');
    }

    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeTermine($query)
    {
        return $query->where('statut', 'termine');
    }

    public function isActif()
    {
        return $this->statut === 'actif';
    }

    public function getMontantMensuelTotalAttribute()
    {
        $total = $this->prix_mensuel;
        if ($this->assurance_incluse) {
            $total += $this->montant_assurance;
        }
        return $total;
    }
}