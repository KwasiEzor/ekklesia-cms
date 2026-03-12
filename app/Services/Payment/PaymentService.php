<?php

namespace App\Services\Payment;

use App\Models\PaymentTransaction;

class PaymentService
{
    public function __construct(
        protected PaymentManager $manager
    ) {}

    /**
     * Create a new transaction and initiate it with the provider.
     */
    public function initiatePayment(array $data): array
    {
        $provider = $data['provider'] ?? $this->manager->getDefaultDriver();

        $transaction = PaymentTransaction::create([
            'tenant_id' => tenant('id'),
            'member_id' => $data['member_id'] ?? null,
            'campus_id' => $data['campus_id'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? config('payments.default_currency', 'XOF'),
            'provider' => $provider,
            'payment_method' => $data['payment_method'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'status' => 'pending',
        ]);

        $paymentRequest = new PaymentRequest(
            amount: (float) $transaction->amount,
            currency: $transaction->currency,
            phone: $transaction->phone_number,
            paymentMethod: $transaction->payment_method,
            returnUrl: $data['return_url'] ?? null,
            notifyUrl: route('api.payments.webhook', ['provider' => $provider]),
            description: $data['description'] ?? 'Offering',
            transactionId: $transaction->uuid,
            customerEmail: $data['customer_email'] ?? null,
            customerName: $data['customer_name'] ?? null,
        );

        $response = $this->manager->driver($provider)->initiate($paymentRequest);

        $transaction->update([
            'status' => $response->status,
            'provider_reference' => $response->providerReference,
            'provider_metadata' => $response->providerMetadata,
        ]);

        if ($response->status === 'failed') {
            $transaction->markAsFailed($response->failureReason ?? 'Payment initiation failed');
        }

        return [
            'transaction' => $transaction->fresh(),
            'payment_url' => $response->paymentUrl,
            'status' => $response->status,
            'failure_reason' => $response->failureReason,
        ];
    }
}
