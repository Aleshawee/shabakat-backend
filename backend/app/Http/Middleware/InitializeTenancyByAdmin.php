<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InitializeTenancyByAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (tenancy()->initialized) {
            return $next($request);
        }

        // Try subdomain-based tenancy first (for production via Apache/Nginx)
        try {
            $host = $request->getHost();
            $tenant = \App\Models\Tenant::whereHas('domains', function ($q) use ($host) {
                $q->where('domain', $host);
            })->first();

            if ($tenant) {
                tenancy()->initialize($tenant);
                return $next($request);
            }
        } catch (\Exception $e) {
            // Ignore errors, fall through to admin-based tenancy
        }

        // Admin-based tenancy (for development via artisan serve)
        $admin = $request->user();
        if ($admin && $admin->tenant_id) {
            $tenant = \App\Models\Tenant::find($admin->tenant_id);
            if ($tenant) {
                tenancy()->initialize($tenant);
                return $next($request);
            }
        }

        // Fallback: use X-Tenant header (for owner managing a specific network)
        if ($slug = $request->header('X-Tenant')) {
            $tenant = \App\Models\Tenant::find($slug);
            if ($tenant) {
                tenancy()->initialize($tenant);
                return $next($request);
            }
        }

        // If still not initialized and admin is owner, default to first active tenant
        if (!tenancy()->initialized && $admin && $admin->isOwner()) {
            $tenant = \App\Models\Tenant::where('status', 'active')->first();
            if ($tenant) {
                tenancy()->initialize($tenant);
            }
        }

        return $next($request);
    }
}
