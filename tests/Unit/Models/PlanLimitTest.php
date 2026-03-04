<?php

use App\Models\PlanLimit;

test('plan limit casts boolean fields correctly', function () {
    $plan = PlanLimit::create([
        'plan_slug' => 'test-plan',
        'name' => 'Test',
        'has_payments' => true,
        'has_sms' => false,
        'has_whatsapp' => true,
        'has_ai' => false,
    ]);

    $plan->refresh();

    expect($plan->has_payments)->toBeTrue()
        ->and($plan->has_sms)->toBeFalse()
        ->and($plan->has_whatsapp)->toBeTrue()
        ->and($plan->has_ai)->toBeFalse();
});

test('plan limit casts integer fields correctly', function () {
    $plan = PlanLimit::create([
        'plan_slug' => 'test-int',
        'name' => 'Test',
        'max_members' => 500,
        'max_storage_mb' => 5120,
        'max_campuses' => 3,
        'price_cents' => 1500,
    ]);

    $plan->refresh();

    expect($plan->max_members)->toBe(500)
        ->and($plan->max_storage_mb)->toBe(5120)
        ->and($plan->max_campuses)->toBe(3)
        ->and($plan->price_cents)->toBe(1500);
});

test('isUnlimited returns true when value is zero', function () {
    $plan = PlanLimit::create([
        'plan_slug' => 'test-unlimited',
        'name' => 'Test',
        'max_members' => 0,
        'max_campuses' => 5,
    ]);

    expect($plan->isUnlimited('max_members'))->toBeTrue()
        ->and($plan->isUnlimited('max_campuses'))->toBeFalse();
});

test('formatted_price returns free for zero price', function () {
    $plan = PlanLimit::create([
        'plan_slug' => 'test-free',
        'name' => 'Free',
        'price_cents' => 0,
    ]);

    expect($plan->formatted_price)->toBe(__('billing.free'));
});

test('formatted_price returns formatted price with currency', function () {
    $plan = PlanLimit::create([
        'plan_slug' => 'test-paid',
        'name' => 'Basic',
        'price_cents' => 1500,
        'currency' => 'USD',
    ]);

    expect($plan->formatted_price)->toBe('15.00 USD/mo');
});
