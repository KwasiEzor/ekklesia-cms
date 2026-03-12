<?php

use App\Models\Member;
use App\Models\PlanLimit;
use App\Models\Tenant;
use App\Services\Billing\PlanLimitsEnforcer;
use App\Services\Payment\PaymentManager;

beforeEach(function () {
    // Clear any existing data
    Member::query()->delete();
    Tenant::query()->delete();
    PlanLimit::query()->delete();
});

test('PlanLimitsEnforcer bug: queries not tenant-scoped', function () {
    // Setup Tenant A with a limit of 3 members and 1 member
    $tenantA = Tenant::create(['id' => 'tenant-a', 'name' => 'Tenant A', 'slug' => 'tenant-a', 'plan_slug' => 'free']);
    PlanLimit::create([
        'plan_slug' => 'free',
        'name' => 'Free',
        'max_members' => 3,
        'max_campuses' => 1,
    ]);

    // Create 1 member for Tenant A
    Member::factory()->create(['tenant_id' => $tenantA->id]);

    // Setup Tenant B with 5 members
    $tenantB = Tenant::create(['id' => 'tenant-b', 'name' => 'Tenant B', 'slug' => 'tenant-b', 'plan_slug' => 'unlimited']);
    Member::factory()->count(5)->create(['tenant_id' => $tenantB->id]);

    $enforcer = new PlanLimitsEnforcer;

    // Initialize tenancy to Tenant B
    tenancy()->initialize($tenantB);

    // BUG: If we check for Tenant A while Tenant B is initialized,
    // it will count Tenant B's members if it uses Member::count()
    // It should be FALSE for Tenant A (1 < 3)
    // But if it's broken, it might return TRUE because it counts 5 members (or 6 total).

    expect($enforcer->memberLimitReached($tenantA))->toBeFalse();
});

test('PaymentManager bug: getSetting() broken with VirtualColumn', function () {
    // Manually set up a tenant in the DB with payment_provider in the 'data' JSON column
    $tenantId = 'tenant-test';
    \DB::table('tenants')->insert([
        'id' => $tenantId,
        'name' => 'Test Tenant',
        'slug' => 'test-tenant',
        'data' => json_encode([
            'payment_provider' => 'stripe',
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $tenant = Tenant::find($tenantId);
    tenancy()->initialize($tenant);

    // Verify getSetting() works
    expect($tenant->getSetting('payment_provider'))->toBe('stripe');

    $manager = new PaymentManager(app());

    // If getSetting is broken, it might return the default 'cinetpay' instead of 'stripe'
    expect($manager->getDefaultDriver())->toBe('stripe');
});
