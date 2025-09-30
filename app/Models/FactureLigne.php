<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactureLigne extends Model
{
    use HasFactory;

    protected $fillable = [
        'facture_id',
        'designation',
        'quantite',
        'prix_unitaire',
        'taux_tva',
        'montant_ht',
        'montant_tva',
        'montant_ttc',
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'taux_tva' => 'decimal:2',
        'montant_ht' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($ligne) {
            $ligne->montant_ht = $ligne->quantite * $ligne->prix_unitaire;
            $ligne->montant_tva = $ligne->montant_ht * ($ligne->taux_tva / 100);
            $ligne->montant_ttc = $ligne->montant_ht + $ligne->montant_tva;
        });
    }
}