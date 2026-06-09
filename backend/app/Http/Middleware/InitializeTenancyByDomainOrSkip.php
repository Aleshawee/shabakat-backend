<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InitializeTenancyByDomainOrSkip
{
    public function handle(Request $request, Closure $next)
    {
        if (tenancy()->initialized) {
            Log::info('TENANCY_DEBUG: Already initialized');
            return $next($request);
        }

        try {
            $host = $request->getHost();
            Log::info('TENANCY_DEBUG: Checking domain', ['host' => $host]);
            $tenant = \App\Models\Tenant::whereHas('domains', function ($q) use ($host) {
                $q->where('domain', $host);
            })->first();

            if ($tenant) {
                Log::info('TENANCY_DEBUG: Found by domain', ['id' => $tenant->id]);
                tenancy()->initialize($tenant);
                Log::info('TENANCY_DEBUG: Initialized by domain', ['init' => tenancy()->initialized, 'db' => config('database.default')]);
                return $next($request);
            }
            Log::info('TENANCY_DEBUG: No domain match');
        } catch (\Exception $e) {
            Log::warning('TENANCY_DEBUG: Domain exception', ['msg' => $e->getMessage()]);
        }

        $slug = $request->header('X-Tenant');
        Log::info('TENANCY_DEBUG: X-Tenant', ['slug' => $slug]);

        if ($slug) {
            $tenant = \App\Models\Tenant::find($slug);
            Log::info('TENANCY_DEBUG: find result', ['found' => $tenant ? $tenant->id : null]);

            if ($tenant) {
                tenancy()->initialize($tenant);
                Log::info('TENANCY_DEBUG: Initialized by header', ['init' => tenancy()->initialized, 'db' => config('database.default')]);
            }
        }

        Log::info('TENANCY_DEBUG: After middleware', ['init' => tenancy()->initialized]);

        return $next($request);
    }
}
