<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'prospect_id',
        'type_client',
        'nom',
        'prenom',
        'raison_sociale',
        'email',
        'telephone',
        'telephone_urgence',
        'adresse',
        'code_postal',
        'ville',
        'pays',
        'siret',
        'date_naissance',
        'piece_identite_type',
        'piece_identite_numero',
        'contact_urgence_nom',
        'contact_urgence_telephone',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'is_active' => 'boolean',
    ];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function contrats()
    {
        return $this->hasMany(Contrat::class);
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    public function documents()
    {
        return $this->hasMany(ClientDocument::class);
    }

    public function mandatsSepa()
    {
        return $this->hasMany(MandatSepa::class);
    }

    public function getFullNameAttribute()
    {
        if ($this->type_client === 'particulier') {
            return $this->prenom . ' ' . $this->nom;
        }
        return $this->raison_sociale;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}