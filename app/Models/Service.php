<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'type_service',
        'prix',
        'unite',
        'facturable',
        'obligatoire',
        'is_active',
    ];

    protected $casts = [
        'prix' => 'decimal:2',
        'facturable' => 'boolean',
        'obligatoire' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function contratsServices()
    {
        return $this->hasMany(ContratService::class);
    }

    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFacturable($query)
    {
        return $query->where('facturable', true);
    }

    public function scopeObligatoire($query)
    {
        return $query->where('obligatoire', true);
    }

    public function getLibelleTypeAttribute()
    {
        $types = [
            'assurance' => 'Assurance',
            'materiel' => 'Matériel',
            'manutention' => 'Manutention',
            'nettoyage' => 'Nettoyage',
            'securite' => 'Sécurité',
            'autre' => 'Autre'
        ];

        return $types[$this->type_service] ?? $this->type_service;
    }

    public function getPrixFormateAttribute()
    {
        return number_format($this->prix, 2) . '€' . ($this->unite ? '/' . $this->unite : '');
    }
}