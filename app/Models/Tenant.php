<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom_entreprise',
        'slug',
        'email',
        'telephone',
        'adresse',
        'code_postal',
        'ville',
        'pays',
        'siret',
        'plan',
        'prix_mensuel',
        'max_boxes',
        'max_users',
        'date_debut_abonnement',
        'date_fin_abonnement',
        'statut_abonnement',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'prix_mensuel' => 'decimal:2',
        'max_boxes' => 'integer',
        'max_users' => 'integer',
        'date_debut_abonnement' => 'date',
        'date_fin_abonnement' => 'date',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from nom_entreprise
        static::creating(function ($tenant) {
            if (empty($tenant->slug)) {
                $tenant->slug = Str::slug($tenant->nom_entreprise);

                // Ensure unique slug
                $count = 1;
                while (static::where('slug', $tenant->slug)->exists()) {
                    $tenant->slug = Str::slug($tenant->nom_entreprise) . '-' . $count;
                    $count++;
                }
            }
        });
    }

    // Relations

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function adminUsers()
    {
        return $this->hasMany(User::class)->where('type_user', 'admin_tenant');
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function boxes()
    {
        return $this->hasMany(Box::class);
    }

    public function boxFamilles()
    {
        return $this->hasMany(BoxFamille::class);
    }

    public function emplacements()
    {
        return $this->hasMany(Emplacement::class);
    }

    public function contrats()
    {
        return $this->hasMany(Contrat::class);
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    public function reglements()
    {
        return $this->hasMany(Reglement::class);
    }

    public function mandatsSepa()
    {
        return $this->hasMany(MandatSepa::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function interventions()
    {
        return $this->hasMany(Intervention::class);
    }

    public function rappels()
    {
        return $this->hasMany(Rappel::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('statut_abonnement', 'actif');
    }

    public function scopeExpired($query)
    {
        return $query->where('statut_abonnement', 'expire')
                    ->orWhere(function ($q) {
                        $q->whereNotNull('date_fin_abonnement')
                          ->where('date_fin_abonnement', '<', now());
                    });
    }

    public function scopeSuspended($query)
    {
        return $query->where('statut_abonnement', 'suspendu');
    }

    // Helper Methods

    public function isActive()
    {
        return $this->is_active && $this->statut_abonnement === 'actif';
    }

    public function isExpired()
    {
        return $this->statut_abonnement === 'expire' ||
               ($this->date_fin_abonnement && $this->date_fin_abonnement->isPast());
    }

    public function isSuspended()
    {
        return $this->statut_abonnement === 'suspendu';
    }

    public function canAddBox()
    {
        return $this->boxes()->count() < $this->max_boxes;
    }

    public function canAddUser()
    {
        return $this->users()->where('type_user', '!=', 'superadmin')->count() < $this->max_users;
    }

    public function getRemainingBoxes()
    {
        return max(0, $this->max_boxes - $this->boxes()->count());
    }

    public function getRemainingUsers()
    {
        return max(0, $this->max_users - $this->users()->where('type_user', '!=', 'superadmin')->count());
    }

    public function getUsagePercentage($type = 'boxes')
    {
        if ($type === 'boxes') {
            return $this->max_boxes > 0 ? ($this->boxes()->count() / $this->max_boxes) * 100 : 0;
        }

        if ($type === 'users') {
            $userCount = $this->users()->where('type_user', '!=', 'superadmin')->count();
            return $this->max_users > 0 ? ($userCount / $this->max_users) * 100 : 0;
        }

        return 0;
    }

    public function getDaysUntilExpiration()
    {
        if (!$this->date_fin_abonnement) {
            return null;
        }

        return now()->diffInDays($this->date_fin_abonnement, false);
    }

    public function getPlanName()
    {
        $plans = [
            'gratuit' => 'Gratuit',
            'starter' => 'Starter',
            'business' => 'Business',
            'enterprise' => 'Enterprise',
        ];

        return $plans[$this->plan] ?? $this->plan;
    }

    public function getStatutBadge()
    {
        $badges = [
            'actif' => '<span class="badge bg-success">Actif</span>',
            'suspendu' => '<span class="badge bg-warning">Suspendu</span>',
            'expire' => '<span class="badge bg-danger">Expiré</span>',
            'annule' => '<span class="badge bg-secondary">Annulé</span>',
        ];

        return $badges[$this->statut_abonnement] ?? '<span class="badge bg-secondary">' . $this->statut_abonnement . '</span>';
    }

    // Settings Helpers

    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
    }
}