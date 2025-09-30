<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MandatSepa extends Model
{
    use HasFactory;

    protected $table = 'mandats_sepa';

    protected $fillable = [
        'client_id',
        'contrat_id',
        'rum',
        'type_mandat',
        'statut',
        'date_signature',
        'date_premiere_utilisation',
        'date_derniere_utilisation',
        'iban',
        'bic',
        'titulaire_compte',
        'adresse_debiteur',
        'is_active',
        'motif_inactivation',
    ];

    protected $casts = [
        'date_signature' => 'date',
        'date_premiere_utilisation' => 'date',
        'date_derniere_utilisation' => 'date',
        'is_active' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    public function prelevements()
    {
        return $this->hasMany(PrelevementSepa::class);
    }

    public function signatures()
    {
        return $this->morphMany(Signature::class, 'signable');
    }

    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    public function generateRum()
    {
        $this->rum = 'RUM' . str_pad($this->client_id, 6, '0', STR_PAD_LEFT) . date('YmdHis');
        return $this->rum;
    }

    public function isValide()
    {
        return $this->is_active && $this->statut === 'valide';
    }
}