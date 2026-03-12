<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends CashierWebhookController
{
    /**
     * Handle a Stripe webhook call.
     */
    public function handleWebhook(Request $request): Response
    {
        $payload = json_decode($request->getContent(), true);

        if (isset($payload['data']['object']['customer'])) {
            $stripeId = $payload['data']['object']['customer'];
            $tenant = Tenant::where('stripe_id', $stripeId)->first();

            if ($tenant) {
                tenancy()->initialize($tenant);
            }
        }

        return parent::handleWebhook($request);
    }
}
