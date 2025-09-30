<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProspectInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'prospect_id',
        'type_interaction',
        'date_interaction',
        'duree_minutes',
        'contenu',
        'resultat',
        'date_relance_prevue',
        'created_by',
    ];

    protected $casts = [
        'date_interaction' => 'datetime',
        'date_relance_prevue' => 'date',
    ];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getLibelleTypeAttribute()
    {
        $types = [
            'appel_entrant' => 'Appel entrant',
            'appel_sortant' => 'Appel sortant',
            'email' => 'Email',
            'visite' => 'Visite',
            'courrier' => 'Courrier',
            'sms' => 'SMS',
            'autre' => 'Autre'
        ];

        return $types[$this->type_interaction] ?? $this->type_interaction;
    }

    public function getLibelleResultatAttribute()
    {
        $resultats = [
            'interesse' => 'Intéressé',
            'pas_interesse' => 'Pas intéressé',
            'demande_rappel' => 'Demande rappel',
            'rdv_pris' => 'RDV pris',
            'sans_reponse' => 'Sans réponse',
            'autre' => 'Autre'
        ];

        return $resultats[$this->resultat] ?? $this->resultat;
    }
}