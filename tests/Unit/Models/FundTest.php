<?php

use App\Models\Campaign;
use App\Models\Fund;
use App\Models\GivingRecord;
use App\Models\Member;
use App\Models\Tenant;

test('fund hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $fund = Fund::factory()->create(['tenant_id' => $tenant->id]);

    expect($fund->toArray())->not->toHaveKey('tenant_id');
});

test('fund has many giving records', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $fund = Fund::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);

    GivingRecord::factory()->count(3)->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'fund_id' => $fund->id,
    ]);

    expect($fund->givingRecords)->toHaveCount(3);
});

test('fund has many campaigns', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $fund = Fund::factory()->create(['tenant_id' => $tenant->id]);

    Campaign::factory()->count(2)->create([
        'tenant_id' => $tenant->id,
        'fund_id' => $fund->id,
    ]);

    expect($fund->campaigns)->toHaveCount(2);
});

test('fund scope active works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Fund::factory()->create(['tenant_id' => $tenant->id, 'is_active' => true]);
    Fund::factory()->inactive()->create(['tenant_id' => $tenant->id]);

    expect(Fund::active()->count())->toBe(1);
});

test('fund scope ofType works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Fund::factory()->ofType('tithe')->create(['tenant_id' => $tenant->id]);
    Fund::factory()->ofType('offering')->create(['tenant_id' => $tenant->id]);
    Fund::factory()->ofType('tithe')->create(['tenant_id' => $tenant->id]);

    expect(Fund::ofType('tithe')->count())->toBe(2);
});

test('fund casts booleans correctly', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $fund = Fund::factory()->create([
        'tenant_id' => $tenant->id,
        'is_active' => true,
    ]);

    expect($fund->fresh()->is_active)->toBeTrue();
});

test('fund total_received accessor works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $fund = Fund::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);

    GivingRecord::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'fund_id' => $fund->id,
        'amount' => 10000,
    ]);
    GivingRecord::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'fund_id' => $fund->id,
        'amount' => 25000,
    ]);

    expect($fund->total_received)->toBe(35000.0);
});
