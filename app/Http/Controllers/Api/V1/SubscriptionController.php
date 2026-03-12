<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PlanLimitResource;
use App\Http\Resources\V1\SubscriptionResource;
use App\Models\PlanLimit;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Get current subscription status.
     */
    public function status(Request $request): JsonResponse
    {
        $tenant = $this->getTenant();
        
        $subscription = $tenant->subscription('default');
        $plan = $tenant->planLimits();

        return response()->json([
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'plan_slug' => $tenant->currentPlanSlug(),
            ],
            'subscription' => $subscription ? new SubscriptionResource($subscription) : null,
            'plan' => $plan ? new PlanLimitResource($plan) : null,
        ]);
    }

    /**
     * List available plans.
     */
    public function plans(): JsonResponse
    {
        $plans = PlanLimit::orderBy('price_cents')->get();

        return response()->json([
            'data' => PlanLimitResource::collection($plans),
        ]);
    }

    /**
     * Subscribe to a plan (Checkout session).
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'plan_slug' => 'required|exists:plan_limits,plan_slug',
        ]);

        $tenant = $this->getTenant();
        $plan = PlanLimit::where('plan_slug', $request->input('plan_slug'))->first();

        if (! $plan || ! $plan->stripe_price_id) {
            return response()->json(['message' => 'This plan is not available for online purchase.'], 422);
        }

        $checkout = $tenant->newSubscription('default', $plan->stripe_price_id)
            ->checkout([
                'success_url' => $request->input('success_url', config('app.url')),
                'cancel_url' => $request->input('cancel_url', config('app.url')),
            ]);

        return response()->json([
            'checkout_url' => $checkout->url,
        ]);
    }

    /**
     * Get billing portal URL.
     */
    public function portal(Request $request): JsonResponse
    {
        $tenant = $this->getTenant();

        if (! $tenant->hasStripeId()) {
            return response()->json(['message' => 'Tenant has no billing history yet.'], 404);
        }

        $url = $tenant->billingPortalUrl($request->input('return_url', config('app.url')));

        return response()->json([
            'portal_url' => $url,
        ]);
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Request $request): JsonResponse
    {
        $tenant = $this->getTenant();
        $subscription = $tenant->subscription('default');

        if (! $subscription || $subscription->canceled()) {
            return response()->json(['message' => 'No active subscription found to cancel.'], 422);
        }

        $subscription->cancel();

        return response()->json(['message' => 'Subscription cancelled successfully.']);
    }

    /**
     * Resume subscription.
     */
    public function resume(Request $request): JsonResponse
    {
        $tenant = $this->getTenant();
        $subscription = $tenant->subscription('default');

        if (! $subscription || ! $subscription->onGracePeriod()) {
            return response()->json(['message' => 'Subscription is not on grace period.'], 422);
        }

        $subscription->resume();

        return response()->json(['message' => 'Subscription resumed successfully.']);
    }

    private function getTenant(): Tenant
    {
        // When using InitializeTenancyByUser, the tenant is available via tenant() or Filament::getTenant()
        return Filament::getTenant() ?? tenant();
    }
}
