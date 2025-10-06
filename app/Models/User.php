<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active',
        'last_login_at',
        'tenant_id',
        'type_user',
        'client_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relations

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function notificationSettings()
    {
        return $this->hasOne(NotificationSetting::class);
    }

    // Scopes

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeSuperAdmins($query)
    {
        return $query->where('type_user', 'superadmin');
    }

    public function scopeAdminTenants($query)
    {
        return $query->where('type_user', 'admin_tenant');
    }

    public function scopeClientFinaux($query)
    {
        return $query->where('type_user', 'client_final');
    }

    // Helper Methods

    public function isSuperAdmin()
    {
        return $this->type_user === 'superadmin';
    }

    public function isAdminTenant()
    {
        return $this->type_user === 'admin_tenant';
    }

    public function isClientFinal()
    {
        return $this->type_user === 'client_final';
    }

    public function canManageTenant()
    {
        return $this->isSuperAdmin();
    }

    public function canAccessTenant($tenantId)
    {
        return $this->isSuperAdmin() || $this->tenant_id == $tenantId;
    }

    public function getTypeUserLabel()
    {
        $labels = [
            'superadmin' => 'Super Administrateur',
            'admin_tenant' => 'Administrateur Entreprise',
            'client_final' => 'Client Final',
        ];

        return $labels[$this->type_user] ?? $this->type_user;
    }

}