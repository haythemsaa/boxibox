<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class AccessCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'client_id',
        'box_id',
        'contrat_id',
        'code_pin',
        'qr_code_data',
        'qr_code_path',
        'type',
        'statut',
        'date_debut',
        'date_fin',
        'temporaire',
        'jours_autorises',
        'heure_debut',
        'heure_fin',
        'max_utilisations',
        'nb_utilisations',
        'notes',
        'derniere_utilisation',
    ];

    protected $casts = [
        'jours_autorises' => 'array',
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'derniere_utilisation' => 'datetime',
        'temporaire' => 'boolean',
    ];

    /**
     * Relations
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function box()
    {
        return $this->belongsTo(Box::class);
    }

    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    public function logs()
    {
        return $this->hasMany(AccessLog::class);
    }

    /**
     * Scopes
     */
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeExpire($query)
    {
        return $query->where('statut', 'expire');
    }

    public function scopeValide($query)
    {
        return $query->where('statut', 'actif')
            ->where(function($q) {
                $q->whereNull('date_fin')
                  ->orWhere('date_fin', '>', now());
            });
    }

    /**
     * Génération automatique code PIN à 6 chiffres unique
     */
    public static function generateUniquePinCode()
    {
        do {
            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('code_pin', $code)->exists());

        return $code;
    }

    /**
     * Génération QR Code
     */
    public function generateQRCode()
    {
        // Génération des données uniques pour le QR code
        $this->qr_code_data = Str::uuid()->toString();

        // Création du QR code image
        $qrCodePath = 'qr_codes/' . $this->client_id . '_' . time() . '.svg';
        $qrCodeFullPath = storage_path('app/public/' . $qrCodePath);

        // Créer le dossier si nécessaire
        if (!file_exists(dirname($qrCodeFullPath))) {
            mkdir(dirname($qrCodeFullPath), 0755, true);
        }

        // Génération du QR code
        QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($this->qr_code_data, $qrCodeFullPath);

        $this->qr_code_path = $qrCodePath;
        $this->save();

        return $qrCodePath;
    }

    /**
     * Vérifier si le code est valide pour un accès
     */
    public function isValid()
    {
        // Vérifier le statut
        if ($this->statut !== 'actif') {
            return ['valid' => false, 'reason' => 'Code non actif'];
        }

        // Vérifier la date de validité
        if ($this->date_debut && Carbon::parse($this->date_debut)->isFuture()) {
            return ['valid' => false, 'reason' => 'Code pas encore actif'];
        }

        if ($this->date_fin && Carbon::parse($this->date_fin)->isPast()) {
            return ['valid' => false, 'reason' => 'Code expiré'];
        }

        // Vérifier le nombre d'utilisations
        if ($this->max_utilisations && $this->nb_utilisations >= $this->max_utilisations) {
            return ['valid' => false, 'reason' => 'Limite d\'utilisations atteinte'];
        }

        // Vérifier les jours autorisés
        if ($this->jours_autorises) {
            $jourActuel = strtolower(Carbon::now()->locale('fr')->dayName);
            if (!in_array($jourActuel, array_map('strtolower', $this->jours_autorises))) {
                return ['valid' => false, 'reason' => 'Jour non autorisé'];
            }
        }

        // Vérifier les heures autorisées
        if ($this->heure_debut && $this->heure_fin) {
            $heureActuelle = Carbon::now()->format('H:i:s');
            if ($heureActuelle < $this->heure_debut || $heureActuelle > $this->heure_fin) {
                return ['valid' => false, 'reason' => 'Hors plage horaire autorisée'];
            }
        }

        return ['valid' => true, 'reason' => null];
    }

    /**
     * Enregistrer une utilisation du code
     */
    public function recordUsage()
    {
        $this->increment('nb_utilisations');
        $this->derniere_utilisation = now();
        $this->save();

        // Vérifier si le code doit expirer après utilisation
        if ($this->max_utilisations && $this->nb_utilisations >= $this->max_utilisations) {
            $this->statut = 'expire';
            $this->save();
        }
    }

    /**
     * Révoquer le code
     */
    public function revoke($reason = null)
    {
        $this->statut = 'revoque';
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Révoqué: $reason";
        }
        $this->save();
    }

    /**
     * Suspendre temporairement le code
     */
    public function suspend($reason = null)
    {
        $this->statut = 'suspendu';
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Suspendu: $reason";
        }
        $this->save();
    }

    /**
     * Réactiver un code suspendu
     */
    public function reactivate()
    {
        if ($this->statut === 'suspendu') {
            $this->statut = 'actif';
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Réactivé le " . now()->format('d/m/Y H:i');
            $this->save();
        }
    }
}
