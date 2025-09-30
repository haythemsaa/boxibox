<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'niveau',
        'zone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function boxes()
    {
        return $this->hasMany(Box::class);
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

    public function getTauxOccupationAttribute()
    {
        $total = $this->getNombreBoxesAttribute();
        $libres = $this->getNombreBoxesLibresAttribute();

        return $total > 0 ? round((($total - $libres) / $total) * 100, 1) : 0;
    }
}