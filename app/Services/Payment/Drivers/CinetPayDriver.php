<?php

namespace App\Services\Payment\Drivers;

use App\Services\Payment\PaymentDriverInterface;
use App\Services\Payment\PaymentRequest;
use App\Services\Payment\PaymentResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CinetPayDriver implements PaymentDriverInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $siteId,
        private readonly string $secretKey,
        private readonly string $baseUrl,
    ) {}

    public function initiate(PaymentRequest $request): PaymentResponse
    {
        try {
            $response = Http::post("{$this->baseUrl}/payment", [
                'apikey' => $this->apiKey,
                'site_id' => $this->siteId,
                'transaction_id' => $request->transactionId,
                'amount' => (int) $request->amount,
                'currency' => $request->currency,
                'description' => $request->description ?? 'Offering',
                'return_url' => $request->returnUrl,
                'notify_url' => $request->notifyUrl,
                'customer_phone_number' => $request->phone,
                'customer_email' => $request->customerEmail,
                'customer_name' => $request->customerName,
                'channels' => 'ALL',
            ]);

            $data = $response->json();

            if (($data['code'] ?? '') === '201') {
                return new PaymentResponse(
                    status: 'pending',
                    providerReference: $data['data']['payment_token'] ?? null,
                    paymentUrl: $data['data']['payment_url'] ?? null,
                    providerMetadata: $data,
                );
            }

            return new PaymentResponse(
                status: 'failed',
                providerMetadata: $data,
                failureReason: $data['message'] ?? 'CinetPay initiation failed',
            );
        } catch (\Throwable $e) {
            Log::error('CinetPay initiation error', ['error' => $e->getMessage()]);

            return new PaymentResponse(
                status: 'failed',
                failureReason: $e->getMessage(),
            );
        }
    }

    public function checkStatus(string $providerReference): PaymentResponse
    {
        try {
            $response = Http::post("{$this->baseUrl}/payment/check", [
                'apikey' => $this->apiKey,
                'site_id' => $this->siteId,
                'transaction_id' => $providerReference,
            ]);

            $data = $response->json();
            $status = $this->mapStatus($data['data']['status'] ?? 'UNKNOWN');

            return new PaymentResponse(
                status: $status,
                providerReference: $providerReference,
                providerMetadata: $data,
                failureReason: $status === 'failed' ? ($data['data']['description'] ?? null) : null,
            );
        } catch (\Throwable $e) {
            Log::error('CinetPay status check error', ['error' => $e->getMessage()]);

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
            'mtn_momo' => 'MTN Mobile Money',
            'orange_money' => 'Orange Money',
            'wave' => 'Wave',
            'moov_money' => 'Moov Money',
            'free_money' => 'Free Money',
        ];
    }

    public function handleWebhook(Request $request): PaymentResponse
    {
        $transactionId = $request->input('cpm_trans_id');
        $signature = $request->input('signature');

        if (! $transactionId) {
            return new PaymentResponse(
                status: 'failed',
                failureReason: 'Missing transaction ID in webhook',
            );
        }

        if ($this->secretKey !== '' && $signature !== null) {
            $expected = hash_hmac('sha256', (string) $transactionId, $this->secretKey);
            if (! hash_equals($expected, $signature)) {
                return new PaymentResponse(
                    status: 'failed',
                    failureReason: 'Invalid webhook signature',
                );
            }
        }

        return $this->checkStatus($transactionId);
    }

    private function mapStatus(string $cinetpayStatus): string
    {
        return match ($cinetpayStatus) {
            'ACCEPTED' => 'completed',
            'REFUSED' => 'failed',
            'CANCELLED' => 'cancelled',
            default => 'pending',
        };
    }
}
