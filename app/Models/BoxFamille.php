<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoxFamille extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'surface_min',
        'surface_max',
        'prix_base',
        'couleur_plan',
        'is_active',
    ];

    protected $casts = [
        'surface_min' => 'decimal:2',
        'surface_max' => 'decimal:2',
        'prix_base' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function boxes()
    {
        return $this->hasMany(Box::class, 'famille_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getNombreBoxesAttribute()
    {
        return $this->boxes()->count();
    }

    public function getNombreBoxesLibresAttribute()
    {
        return $this->boxes()->libre()->count();
    }
}