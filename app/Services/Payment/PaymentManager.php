<?php

namespace App\Services\Payment;

use App\Models\Tenant;
use App\Services\Payment\Drivers\CinetPayDriver;
use App\Services\Payment\Drivers\StripePaymentDriver;
use Illuminate\Support\Manager;

/**
 * @method PaymentDriverInterface driver(?string $driver = null)
 */
class PaymentManager extends Manager
{
    public function getDefaultDriver(): string
    {
        $tenant = tenant();

        if ($tenant instanceof Tenant) {
            return (string) $tenant->getSetting('payment_provider', config('payments.default'));
        }

        return config('payments.default');
    }

    protected function createCinetpayDriver(): CinetPayDriver
    {
        $tenant = tenant();
        $apiKey = $tenant instanceof Tenant
            ? (string) $tenant->getSetting('cinetpay_api_key', config('payments.providers.cinetpay.api_key'))
            : (string) config('payments.providers.cinetpay.api_key');
        $siteId = $tenant instanceof Tenant
            ? (string) $tenant->getSetting('cinetpay_site_id', config('payments.providers.cinetpay.site_id'))
            : (string) config('payments.providers.cinetpay.site_id');
        $secretKey = $tenant instanceof Tenant
            ? (string) $tenant->getSetting('cinetpay_secret_key', config('payments.providers.cinetpay.secret_key'))
            : (string) config('payments.providers.cinetpay.secret_key');

        return new CinetPayDriver(
            apiKey: $apiKey,
            siteId: $siteId,
            secretKey: $secretKey,
            baseUrl: (string) config('payments.providers.cinetpay.base_url'),
        );
    }

    protected function createStripeDriver(): StripePaymentDriver
    {
        $tenant = tenant();
        $secretKey = $tenant instanceof Tenant
            ? (string) $tenant->getSetting('stripe_secret_key', config('payments.providers.stripe.secret_key'))
            : (string) config('payments.providers.stripe.secret_key');

        return new StripePaymentDriver(
            secretKey: $secretKey,
            webhookSecret: config('payments.providers.stripe.webhook_secret') ?? '',
        );
    }
}
