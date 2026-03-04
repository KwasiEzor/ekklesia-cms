<?php

namespace App\Services\Payment;

use Illuminate\Http\Request;

interface PaymentDriverInterface
{
    public function initiate(PaymentRequest $request): PaymentResponse;

    public function checkStatus(string $providerReference): PaymentResponse;

    /**
     * @return array<string, string>
     */
    public function providers(): array;

    public function handleWebhook(Request $request): PaymentResponse;
}
