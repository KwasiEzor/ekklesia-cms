<?php

use App\Models\Campus;
use App\Models\Member;
use App\Models\PlanLimit;
use App\Models\Tenant;
use App\Services\Billing\PlanLimitsEnforcer;

test('enforcer checks feature availability', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);
    $tenant->plan_slug = 'premium';
    $tenant->save();

    PlanLimit::create([
        'plan_slug' => 'premium',
        'name' => 'Premium',
        'has_payments' => true,
        'has_sms' => true,
        'has_whatsapp' => true,
        'has_ai' => true,
    ]);

    $enforcer = new PlanLimitsEnforcer;

    expect($enforcer->check($tenant, 'payments'))->toBeTrue()
        ->and($enforcer->check($tenant, 'sms'))->toBeTrue()
        ->and($enforcer->check($tenant, 'whatsapp'))->toBeTrue()
        ->and($enforcer->check($tenant, 'ai'))->toBeTrue();
});

test('enforcer denies features on free plan', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    PlanLimit::create([
        'plan_slug' => 'free',
        'name' => 'Free',
        'has_payments' => false,
        'has_sms' => false,
        'has_whatsapp' => false,
        'has_ai' => false,
    ]);

    $enforcer = new PlanLimitsEnforcer;

    expect($enforcer->check($tenant, 'payments'))->toBeFalse()
        ->and($enforcer->check($tenant, 'sms'))->toBeFalse()
        ->and($enforcer->check($tenant, 'whatsapp'))->toBeFalse()
        ->and($enforcer->check($tenant, 'ai'))->toBeFalse();
});

test('enforcer detects member limit reached', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    PlanLimit::create([
        'plan_slug' => 'free',
        'name' => 'Free',
        'max_members' => 3,
    ]);

    Member::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $enforcer = new PlanLimitsEnforcer;

    expect($enforcer->memberLimitReached($tenant))->toBeTrue();
});

test('enforcer allows members when under limit', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    PlanLimit::create([
        'plan_slug' => 'free',
        'name' => 'Free',
        'max_members' => 50,
    ]);

    Member::factory()->count(2)->create(['tenant_id' => $tenant->id]);

    $enforcer = new PlanLimitsEnforcer;

    expect($enforcer->memberLimitReached($tenant))->toBeFalse();
});

test('enforcer allows unlimited members when max is zero', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);
    $tenant->plan_slug = 'enterprise';
    $tenant->save();

    PlanLimit::create([
        'plan_slug' => 'enterprise',
        'name' => 'Enterprise',
        'max_members' => 0,
    ]);

    Member::factory()->count(10)->create(['tenant_id' => $tenant->id]);

    $enforcer = new PlanLimitsEnforcer;

    expect($enforcer->memberLimitReached($tenant))->toBeFalse();
});

test('enforcer detects campus limit reached', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    PlanLimit::create([
        'plan_slug' => 'free',
        'name' => 'Free',
        'max_campuses' => 1,
    ]);

    Campus::factory()->create(['tenant_id' => $tenant->id]);

    $enforcer = new PlanLimitsEnforcer;

    expect($enforcer->campusLimitReached($tenant))->toBeTrue();
});

test('enforcer returns usage summary', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    PlanLimit::create([
        'plan_slug' => 'free',
        'name' => 'Free',
        'max_members' => 50,
        'max_storage_mb' => 500,
        'max_campuses' => 1,
    ]);

    Member::factory()->count(5)->create(['tenant_id' => $tenant->id]);

    $enforcer = new PlanLimitsEnforcer;
    $usage = $enforcer->getUsageSummary($tenant);

    expect($usage)->toHaveKeys(['members', 'campuses', 'storage_mb'])
        ->and($usage['members']['current'])->toBe(5)
        ->and($usage['members']['limit'])->toBe(50)
        ->and($usage['members']['unlimited'])->toBeFalse();
});
