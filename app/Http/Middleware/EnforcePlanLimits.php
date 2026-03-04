<?php

namespace App\Http\Middleware;

use App\Services\Billing\PlanLimitsEnforcer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePlanLimits
{
    public function __construct(
        protected PlanLimitsEnforcer $enforcer,
    ) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $tenant = tenant();

        if (! $tenant) {
            return $next($request);
        }

        if (! $this->enforcer->check($tenant, $feature)) {
            return response()->json([
                'message' => __('billing.feature_not_available'),
                'feature' => $feature,
                'upgrade_required' => true,
            ], 403);
        }

        return $next($request);
    }
}
