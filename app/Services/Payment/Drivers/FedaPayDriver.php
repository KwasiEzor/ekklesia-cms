<?php

namespace App\Services\Payment\Drivers;

use App\Services\Payment\PaymentDriverInterface;
use App\Services\Payment\PaymentRequest;
use App\Services\Payment\PaymentResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FedaPayDriver implements PaymentDriverInterface
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $baseUrl,
    ) {}

    public function initiate(PaymentRequest $request): PaymentResponse
    {
        try {
            $response = Http::withToken($this->secretKey)
                ->post("{$this->baseUrl}/transactions", [
                    'amount' => (int) $request->amount,
                    'currency' => ['iso' => $request->currency],
                    'description' => $request->description ?? 'Offering',
                    'callback_url' => $request->returnUrl,
                    'customer' => [
                        'firstname' => $request->customerName ?: 'Member',
                        'lastname' => ' ',
                        'email' => $request->customerEmail ?: 'member@example.com',
                        'phone_number' => ['number' => $request->phone],
                    ],
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['v1/transaction']['id'])) {
                $transactionId = $data['v1/transaction']['id'];

                // Create a payment token/link
                $tokenResponse = Http::withToken($this->secretKey)
                    ->post("{$this->baseUrl}/transactions/{$transactionId}/token");

                $tokenData = $tokenResponse->json();

                return new PaymentResponse(
                    status: 'pending',
                    providerReference: (string) $transactionId,
                    paymentUrl: $tokenData['v1/token']['url'] ?? null,
                    providerMetadata: $data,
                );
            }

            return new PaymentResponse(
                status: 'failed',
                providerMetadata: $data,
                failureReason: $data['message'] ?? 'FedaPay initiation failed',
            );
        } catch (\Throwable $e) {
            Log::error('FedaPay initiation error', ['error' => $e->getMessage()]);

            return new PaymentResponse(
                status: 'failed',
                failureReason: $e->getMessage(),
            );
        }
    }

    public function checkStatus(string $providerReference): PaymentResponse
    {
        try {
            $response = Http::withToken($this->secretKey)
                ->get("{$this->baseUrl}/transactions/{$providerReference}");

            $data = $response->json();
            $fedapayStatus = $data['v1/transaction']['status'] ?? 'UNKNOWN';
            $status = $this->mapStatus($fedapayStatus);

            return new PaymentResponse(
                status: $status,
                providerReference: $providerReference,
                providerMetadata: $data,
                failureReason: $status === 'failed' ? ($data['message'] ?? null) : null,
            );
        } catch (\Throwable $e) {
            Log::error('FedaPay status check error', ['error' => $e->getMessage()]);

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
            'mtn' => 'MTN Mobile Money',
            'moov' => 'Moov Money',
            'togocom' => 'T-Money',
            'card' => 'Credit Card',
        ];
    }

    public function handleWebhook(Request $request): PaymentResponse
    {
        // FedaPay webhooks carry the transaction ID in the entity
        $data = $request->input('entity');
        $transactionId = $data['id'] ?? null;

        if (! $transactionId) {
            return new PaymentResponse(
                status: 'failed',
                failureReason: 'Missing transaction ID in FedaPay webhook',
            );
        }

        return $this->checkStatus((string) $transactionId);
    }

    private function mapStatus(string $fedapayStatus): string
    {
        return match ($fedapayStatus) {
            'approved' => 'completed',
            'declined', 'canceled' => 'failed',
            'transferred' => 'completed',
            'refunded' => 'refunded',
            default => 'pending',
        };
    }
}
