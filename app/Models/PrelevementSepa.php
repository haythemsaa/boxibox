<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrelevementSepa extends Model
{
    use HasFactory;

    protected $table = 'prelevements_sepa';

    protected $fillable = [
        'mandat_sepa_id',
        'facture_id',
        'montant',
        'date_prelevement',
        'date_valeur',
        'statut',
        'reference_end_to_end',
        'fichier_sepa',
        'code_retour',
        'libelle_retour',
        'date_retour',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_prelevement' => 'date',
        'date_valeur' => 'date',
        'date_retour' => 'date',
    ];

    public function mandatSepa()
    {
        return $this->belongsTo(MandatSepa::class);
    }

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    public function scopeReussis($query)
    {
        return $query->where('statut', 'reussi');
    }

    public function scopeRejetes($query)
    {
        return $query->where('statut', 'rejete');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function isReussi()
    {
        return $this->statut === 'reussi';
    }

    public function isRejete()
    {
        return $this->statut === 'rejete';
    }

    public function generateEndToEndReference()
    {
        $this->reference_end_to_end = 'E2E' . str_pad($this->id, 12, '0', STR_PAD_LEFT);
        return $this->reference_end_to_end;
    }
}