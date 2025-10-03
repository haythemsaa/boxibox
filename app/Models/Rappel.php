<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rappel extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'facture_id',
        'client_id',
        'niveau',
        'mode_envoi',
        'date_rappel',
        'date_envoi',
        'statut',
        'montant_du',
        'montant',
        'document_path',
        'notes',
    ];

    protected $casts = [
        'date_rappel' => 'date',
        'date_envoi' => 'datetime',
        'montant_du' => 'decimal:2',
        'montant' => 'decimal:2',
    ];

    // Relations
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Scopes
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeEnvoye($query)
    {
        return $query->where('statut', 'envoye');
    }

    public function scopeRegle($query)
    {
        return $query->where('statut', 'regle');
    }

    public function scopeByNiveau($query, $niveau)
    {
        return $query->where('niveau', $niveau);
    }
}
