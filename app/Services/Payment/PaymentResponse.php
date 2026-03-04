<?php

namespace App\Services\Payment;

class PaymentResponse
{
    public function __construct(
        public string $status,
        public ?string $providerReference = null,
        public ?string $paymentUrl = null,
        public ?array $providerMetadata = null,
        public ?string $failureReason = null,
    ) {}
}
