<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relance extends Model
{
    use HasFactory;

    protected $fillable = [
        'facture_id',
        'client_id',
        'type_relance',
        'niveau',
        'date_envoi',
        'date_prevue',
        'statut',
        'montant_penalite',
        'contenu',
        'mode_envoi',
        'adresse_envoi',
        'created_by',
    ];

    protected $casts = [
        'date_envoi' => 'date',
        'date_prevue' => 'date',
        'montant_penalite' => 'decimal:2',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeEnvoyees($query)
    {
        return $query->where('statut', 'envoyee');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function isEnvoyee()
    {
        return $this->statut === 'envoyee';
    }

    public function getLibelleNiveauAttribute()
    {
        $niveaux = [
            1 => 'Première relance',
            2 => 'Deuxième relance',
            3 => 'Mise en demeure',
            4 => 'Procédure judiciaire'
        ];

        return $niveaux[$this->niveau] ?? 'Relance niveau ' . $this->niveau;
    }
}