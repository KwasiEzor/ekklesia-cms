<?php

use App\Services\Payment\Drivers\FedaPayDriver;
use App\Services\Payment\PaymentRequest;
use Illuminate\Support\Facades\Http;

test('fedapay driver initiates payment successfully', function () {
    Http::fake([
        'api.fedapay.com/v1/transactions' => Http::response([
            'v1/transaction' => ['id' => 12345],
        ], 200),
        'api.fedapay.com/v1/transactions/12345/token' => Http::response([
            'v1/token' => ['url' => 'https://pay.fedapay.com/token123'],
        ], 200),
    ]);

    $driver = new FedaPayDriver('sk_test', 'https://api.fedapay.com/v1');

    $request = new PaymentRequest(
        amount: 1000,
        currency: 'XOF',
        phone: '+22890000000',
        paymentMethod: 'momo',
        returnUrl: 'https://example.com/return',
        notifyUrl: 'https://example.com/notify',
        description: 'Test Donation',
        transactionId: 'txn-123'
    );

    $response = $driver->initiate($request);

    expect($response->status)->toBe('pending')
        ->and($response->providerReference)->toBe('12345')
        ->and($response->paymentUrl)->toBe('https://pay.fedapay.com/token123');
});

test('fedapay driver handles initiation failure', function () {
    Http::fake([
        'api.fedapay.com/v1/transactions' => Http::response([
            'message' => 'Invalid amount',
        ], 400),
    ]);

    $driver = new FedaPayDriver('sk_test', 'https://api.fedapay.com/v1');

    $request = new PaymentRequest(
        amount: 10,
        currency: 'XOF',
        phone: '+22890000000',
        paymentMethod: 'momo',
        returnUrl: 'https://example.com/return',
        notifyUrl: 'https://example.com/notify',
        description: 'Test Donation',
        transactionId: 'txn-123'
    );

    $response = $driver->initiate($request);

    expect($response->status)->toBe('failed')
        ->and($response->failureReason)->toBe('Invalid amount');
});

test('fedapay driver lists providers', function () {
    $driver = new FedaPayDriver('sk_test', 'https://api.fedapay.com/v1');
    $providers = $driver->providers();

    expect($providers)->toHaveKey('mtn');
});
