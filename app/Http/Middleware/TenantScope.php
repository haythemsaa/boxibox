<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantScope
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // SuperAdmin can see all data
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Apply tenant scope for admin_tenant and client_final
        if ($user->tenant_id) {
            // Add global scope to all tenant-aware models
            $this->applyTenantScope($user->tenant_id);
        }

        return $next($request);
    }

    /**
     * Apply tenant scope to all models
     */
    private function applyTenantScope($tenantId)
    {
        // List of models that should be scoped by tenant
        $models = [
            \App\Models\Client::class,
            \App\Models\Box::class,
            \App\Models\BoxFamille::class,
            \App\Models\Emplacement::class,
            \App\Models\Contrat::class,
            \App\Models\Facture::class,
            \App\Models\Reglement::class,
            \App\Models\MandatSepa::class,
            \App\Models\PrelevementSepa::class,
            \App\Models\Document::class,
            \App\Models\Intervention::class,
            \App\Models\Rappel::class,
        ];

        foreach ($models as $model) {
            if (class_exists($model)) {
                $model::addGlobalScope('tenant', function ($query) use ($tenantId) {
                    $query->where($query->getModel()->getTable() . '.tenant_id', $tenantId);
                });
            }
        }
    }
}