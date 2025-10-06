<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'access_code_id',
        'client_id',
        'box_id',
        'type_acces',
        'methode',
        'statut',
        'date_heure',
        'code_utilise',
        'terminal_id',
        'emplacement',
        'ip_address',
        'user_agent',
        'raison_refus',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'date_heure' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Relations
     */
    public function accessCode()
    {
        return $this->belongsTo(AccessCode::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function box()
    {
        return $this->belongsTo(Box::class);
    }

    /**
     * Scopes
     */
    public function scopeAutorise($query)
    {
        return $query->where('statut', 'autorise');
    }

    public function scopeRefuse($query)
    {
        return $query->where('statut', 'refuse');
    }

    public function scopeEntree($query)
    {
        return $query->where('type_acces', 'entree');
    }

    public function scopeSortie($query)
    {
        return $query->where('type_acces', 'sortie');
    }

    public function scopeAujourdhui($query)
    {
        return $query->whereDate('date_heure', today());
    }

    public function scopeCetteSemaine($query)
    {
        return $query->whereBetween('date_heure', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeCeMois($query)
    {
        return $query->whereMonth('date_heure', now()->month)
            ->whereYear('date_heure', now()->year);
    }

    /**
     * Créer un log d'accès
     */
    public static function logAccess($data)
    {
        return self::create(array_merge($data, [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]));
    }

    /**
     * Vérifier un code PIN et logger
     */
    public static function verifyAndLogPinAccess($pin, $boxId = null, $type = 'entree')
    {
        $accessCode = AccessCode::where('code_pin', $pin)
            ->where('type', 'pin')
            ->first();

        if (!$accessCode) {
            return self::logAccess([
                'client_id' => null,
                'box_id' => $boxId,
                'type_acces' => $type,
                'methode' => 'pin',
                'statut' => 'refuse',
                'code_utilise' => $pin,
                'raison_refus' => 'Code PIN inconnu',
            ]);
        }

        $validation = $accessCode->isValid();

        if (!$validation['valid']) {
            return self::logAccess([
                'access_code_id' => $accessCode->id,
                'client_id' => $accessCode->client_id,
                'box_id' => $boxId ?? $accessCode->box_id,
                'type_acces' => $type,
                'methode' => 'pin',
                'statut' => 'refuse',
                'code_utilise' => $pin,
                'raison_refus' => $validation['reason'],
            ]);
        }

        // Accès autorisé
        $accessCode->recordUsage();

        return self::logAccess([
            'access_code_id' => $accessCode->id,
            'client_id' => $accessCode->client_id,
            'box_id' => $boxId ?? $accessCode->box_id,
            'type_acces' => $type,
            'methode' => 'pin',
            'statut' => 'autorise',
            'code_utilise' => $pin,
        ]);
    }

    /**
     * Vérifier un QR code et logger
     */
    public static function verifyAndLogQrAccess($qrData, $boxId = null, $type = 'entree')
    {
        $accessCode = AccessCode::where('qr_code_data', $qrData)
            ->where('type', 'qr')
            ->first();

        if (!$accessCode) {
            return self::logAccess([
                'client_id' => null,
                'box_id' => $boxId,
                'type_acces' => $type,
                'methode' => 'qr',
                'statut' => 'refuse',
                'code_utilise' => substr($qrData, 0, 20) . '...',
                'raison_refus' => 'QR Code inconnu',
            ]);
        }

        $validation = $accessCode->isValid();

        if (!$validation['valid']) {
            return self::logAccess([
                'access_code_id' => $accessCode->id,
                'client_id' => $accessCode->client_id,
                'box_id' => $boxId ?? $accessCode->box_id,
                'type_acces' => $type,
                'methode' => 'qr',
                'statut' => 'refuse',
                'code_utilise' => substr($qrData, 0, 20) . '...',
                'raison_refus' => $validation['reason'],
            ]);
        }

        // Accès autorisé
        $accessCode->recordUsage();

        return self::logAccess([
            'access_code_id' => $accessCode->id,
            'client_id' => $accessCode->client_id,
            'box_id' => $boxId ?? $accessCode->box_id,
            'type_acces' => $type,
            'methode' => 'qr',
            'statut' => 'autorise',
            'code_utilise' => substr($qrData, 0, 20) . '...',
        ]);
    }
}
