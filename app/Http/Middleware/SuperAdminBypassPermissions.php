<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminBypassPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is SuperAdmin, grant all permissions
        if ($user && $user->isSuperAdmin()) {
            // Make the user act like they have all permissions
            $user->givePermissionTo = function() { return true; };

            // Override the hasPermissionTo method to always return true
            $user->setAttribute('_isSuperAdmin', true);
        }

        return $next($request);
    }
}