<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'type_document',
        'nom_fichier',
        'nom_original',
        'chemin_fichier',
        'taille',
        'mime_type',
        'description',
        'uploaded_by',
    ];

    protected $casts = [
        'taille' => 'integer',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getLibelleTypeAttribute()
    {
        $types = [
            'piece_identite' => 'Pièce d\'identité',
            'justificatif_domicile' => 'Justificatif de domicile',
            'rib' => 'RIB',
            'kbis' => 'Extrait Kbis',
            'assurance' => 'Attestation d\'assurance',
            'mandat_sepa' => 'Mandat SEPA',
            'contrat' => 'Contrat signé',
            'correspondance' => 'Correspondance',
            'autre' => 'Autre'
        ];

        return $types[$this->type_document] ?? $this->type_document;
    }

    public function getTailleFormateeAttribute()
    {
        $bytes = $this->taille;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return $bytes . ' byte';
        } else {
            return '0 bytes';
        }
    }

    public function getUrlAttribute()
    {
        return storage_path('app/' . $this->chemin_fichier);
    }
}