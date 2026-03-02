<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Initialize tenancy from the authenticated user's tenant_id.
 *
 * Used on API routes where the tenant is determined by the
 * authenticated Sanctum token rather than by subdomain.
 */
class InitializeTenancyByUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->tenant_id && ! tenant()) {
            tenancy()->initialize(
                \App\Models\Tenant::find($user->tenant_id)
            );
        }

        return $next($request);
    }
}
