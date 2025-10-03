<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    use HasFactory;

    protected $fillable = [
        'famille_id',
        'emplacement_id',
        'numero',
        'surface',
        'volume',
        'prix_mensuel',
        'statut',
        'coordonnees_x',
        'coordonnees_y',
        'plan_x',
        'plan_y',
        'plan_width',
        'plan_height',
        'description',
        'is_active',
    ];

    protected $casts = [
        'surface' => 'decimal:2',
        'volume' => 'decimal:2',
        'prix_mensuel' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function famille()
    {
        return $this->belongsTo(BoxFamille::class, 'famille_id');
    }

    public function emplacement()
    {
        return $this->belongsTo(Emplacement::class);
    }

    public function contrats()
    {
        return $this->hasMany(Contrat::class);
    }

    public function contratActif()
    {
        return $this->hasOne(Contrat::class)->where('statut', 'actif');
    }

    public function scopeLibre($query)
    {
        return $query->where('statut', 'libre');
    }

    public function scopeOccupe($query)
    {
        return $query->where('statut', 'occupe');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isLibre()
    {
        return $this->statut === 'libre';
    }

    public function isOccupe()
    {
        return $this->statut === 'occupe';
    }
}