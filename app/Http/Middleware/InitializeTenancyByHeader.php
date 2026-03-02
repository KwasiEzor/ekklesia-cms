<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Initialize tenancy from the X-Tenant-ID request header.
 *
 * Used on public API routes (login, register) where
 * no authenticated user exists yet to determine the tenant.
 * Also supports subdomain-based identification as fallback.
 */
class InitializeTenancyByHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        if (tenant()) {
            return $next($request);
        }

        $tenantId = $request->header('X-Tenant-ID');

        if (! $tenantId) {
            abort(422, 'X-Tenant-ID header is required.');
        }

        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            throw new NotFoundHttpException('Tenant not found.');
        }

        tenancy()->initialize($tenant);

        return $next($request);
    }
}
