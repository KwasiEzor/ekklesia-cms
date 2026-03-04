<?php

namespace App\Services\Payment\Drivers;

use App\Services\Payment\PaymentDriverInterface;
use App\Services\Payment\PaymentRequest;
use App\Services\Payment\PaymentResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripePaymentDriver implements PaymentDriverInterface
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $webhookSecret,
    ) {}

    public function initiate(PaymentRequest $request): PaymentResponse
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secretKey);

            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($request->currency),
                        'unit_amount' => (int) ($request->amount * 100),
                        'product_data' => [
                            'name' => $request->description ?? 'Offering',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $request->returnUrl ?? config('app.url').'/payment/success',
                'cancel_url' => $request->returnUrl ?? config('app.url').'/payment/cancel',
                'metadata' => [
                    'transaction_id' => $request->transactionId,
                ],
                'customer_email' => $request->customerEmail,
            ]);

            return new PaymentResponse(
                status: 'pending',
                providerReference: $session->id,
                paymentUrl: $session->url,
                providerMetadata: ['session_id' => $session->id],
            );
        } catch (\Throwable $e) {
            Log::error('Stripe initiation error', ['error' => $e->getMessage()]);

            return new PaymentResponse(
                status: 'failed',
                failureReason: $e->getMessage(),
            );
        }
    }

    public function checkStatus(string $providerReference): PaymentResponse
    {
        try {
            $stripe = new \Stripe\StripeClient($this->secretKey);
            $session = $stripe->checkout->sessions->retrieve($providerReference);

            $status = match ($session->payment_status) {
                'paid' => 'completed',
                'unpaid' => 'pending',
                'no_payment_required' => 'completed',
                default => 'pending',
            };

            return new PaymentResponse(
                status: $status,
                providerReference: $providerReference,
                providerMetadata: [
                    'payment_status' => $session->payment_status,
                    'payment_intent' => $session->payment_intent,
                ],
            );
        } catch (\Throwable $e) {
            Log::error('Stripe status check error', ['error' => $e->getMessage()]);

            return new PaymentResponse(
                status: 'failed',
                providerReference: $providerReference,
                failureReason: $e->getMessage(),
            );
        }
    }

    public function providers(): array
    {
        return [
            'card' => 'Credit/Debit Card',
        ];
    }

    public function handleWebhook(Request $request): PaymentResponse
    {
        try {
            $event = \Stripe\Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                $this->webhookSecret,
            );

            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;

                return new PaymentResponse(
                    status: 'completed',
                    providerReference: $session->id,
                    providerMetadata: [
                        'event_type' => $event->type,
                        'payment_intent' => data_get($session, 'payment_intent'),
                        'transaction_id' => data_get($session, 'metadata.transaction_id'),
                    ],
                );
            }

            return new PaymentResponse(
                status: 'pending',
                providerMetadata: ['event_type' => $event->type],
            );
        } catch (\Throwable $e) {
            Log::error('Stripe webhook error', ['error' => $e->getMessage()]);

            return new PaymentResponse(
                status: 'failed',
                failureReason: $e->getMessage(),
            );
        }
    }
}
