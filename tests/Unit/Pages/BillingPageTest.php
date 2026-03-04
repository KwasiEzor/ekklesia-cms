<?php

use App\Filament\Pages\Billing;
use App\Models\PlanLimit;
use Filament\Support\Enums\Width;

test('billing page uses full content width for premium layout', function () {
    $page = new Billing;

    expect($page->getMaxContentWidth())->toBe(Width::Full);
});

test('billing page shows custom pricing for enterprise plan', function () {
    $page = new Billing;
    $plan = new PlanLimit([
        'plan_slug' => 'enterprise',
        'price_cents' => 0,
        'currency' => 'XOF',
    ]);

    expect($page->formatPlanPrice($plan))->toBe(__('billing.custom_pricing'));
});

test('billing page formats paid plan with plan currency', function () {
    $page = new Billing;
    $plan = new PlanLimit([
        'plan_slug' => 'premium',
        'price_cents' => 450000,
        'currency' => 'XOF',
    ]);

    expect($page->formatPlanPrice($plan))->toContain('XOF')
        ->and($page->formatPlanPrice($plan))->not->toStartWith('$');
});
