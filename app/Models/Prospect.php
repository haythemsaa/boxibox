<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospect extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
        'code_postal',
        'ville',
        'origine',
        'statut',
        'notes',
        'date_contact',
        'date_relance',
        'assigned_to',
        'converted_to_client_at',
    ];

    protected $casts = [
        'date_contact' => 'date',
        'date_relance' => 'date',
        'converted_to_client_at' => 'datetime',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function interactions()
    {
        return $this->hasMany(ProspectInteraction::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('converted_to_client_at');
    }

    public function scopeConverted($query)
    {
        return $query->whereNotNull('converted_to_client_at');
    }
}