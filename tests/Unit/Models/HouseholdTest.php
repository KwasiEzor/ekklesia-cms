<?php

use App\Models\Household;
use App\Models\Member;
use App\Models\Tenant;

test('household casts custom_fields as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $household = Household::factory()->create([
        'tenant_id' => $tenant->id,
        'custom_fields' => ['notes' => 'VIP family'],
    ]);

    expect($household->fresh()->custom_fields)->toBe(['notes' => 'VIP family']);
});

test('household hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $household = Household::factory()->create(['tenant_id' => $tenant->id]);

    expect($household->toArray())->not->toHaveKey('tenant_id');
});

test('household has many members', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $household = Household::factory()->create(['tenant_id' => $tenant->id]);
    Member::factory()->count(3)->create([
        'tenant_id' => $tenant->id,
        'household_id' => $household->id,
    ]);

    expect($household->members)->toHaveCount(3);
});

test('household head returns member with head role', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $household = Household::factory()->create(['tenant_id' => $tenant->id]);
    $head = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'household_id' => $household->id,
        'family_role' => 'head',
    ]);
    Member::factory()->create([
        'tenant_id' => $tenant->id,
        'household_id' => $household->id,
        'family_role' => 'spouse',
    ]);

    expect($household->head()->id)->toBe($head->id);
});

test('member belongs to household', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $household = Household::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'household_id' => $household->id,
        'family_role' => 'child',
    ]);

    expect($member->household->id)->toBe($household->id)
        ->and($member->family_role)->toBe('child');
});

test('household member_count attribute works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $household = Household::factory()->create(['tenant_id' => $tenant->id]);
    Member::factory()->count(4)->create([
        'tenant_id' => $tenant->id,
        'household_id' => $household->id,
    ]);

    expect($household->member_count)->toBe(4);
});
