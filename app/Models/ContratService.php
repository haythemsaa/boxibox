<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContratService extends Model
{
    use HasFactory;

    protected $fillable = [
        'contrat_id',
        'service_id',
        'quantite',
        'prix_unitaire',
        'prix_total',
        'date_debut',
        'date_fin',
        'is_active',
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'prix_total' => 'decimal:2',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'is_active' => 'boolean',
    ];

    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($contratService) {
            $contratService->prix_total = $contratService->quantite * $contratService->prix_unitaire;
        });
    }
}