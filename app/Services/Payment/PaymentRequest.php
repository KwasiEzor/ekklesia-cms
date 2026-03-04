<?php

namespace App\Services\Payment;

class PaymentRequest
{
    public function __construct(
        public float $amount,
        public string $currency = 'XOF',
        public ?string $phone = null,
        public ?string $paymentMethod = null,
        public ?string $returnUrl = null,
        public ?string $notifyUrl = null,
        public ?string $description = null,
        public ?string $transactionId = null,
        public ?string $customerEmail = null,
        public ?string $customerName = null,
    ) {}
}
