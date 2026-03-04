<?php

use App\Services\Payment\Drivers\CinetPayDriver;
use App\Services\Payment\Drivers\StripePaymentDriver;
use App\Services\Payment\PaymentManager;

test('payment manager resolves cinetpay driver by default', function () {
    config(['payments.default' => 'cinetpay']);

    $manager = new PaymentManager(app());
    expect($manager->driver())->toBeInstanceOf(CinetPayDriver::class);
});

test('payment manager resolves stripe driver', function () {
    $manager = new PaymentManager(app());
    expect($manager->driver('stripe'))->toBeInstanceOf(StripePaymentDriver::class);
});

test('payment manager returns default driver from config', function () {
    config(['payments.default' => 'cinetpay']);

    $manager = new PaymentManager(app());
    expect($manager->getDefaultDriver())->toBe('cinetpay');
});

test('cinetpay driver lists mobile money providers', function () {
    $driver = new CinetPayDriver('', '', '', 'https://test.example');
    $providers = $driver->providers();

    expect($providers)->toHaveKey('mtn_momo')
        ->and($providers)->toHaveKey('orange_money')
        ->and($providers)->toHaveKey('wave');
});

test('stripe driver lists card provider', function () {
    $driver = new StripePaymentDriver('', '');
    $providers = $driver->providers();

    expect($providers)->toHaveKey('card');
});
