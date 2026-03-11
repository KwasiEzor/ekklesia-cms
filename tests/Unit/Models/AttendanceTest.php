<?php

use App\Models\Attendance;
use App\Models\Member;
use App\Models\ServiceType;
use App\Models\Tenant;

test('attendance casts custom_fields as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant->id]);

    $attendance = Attendance::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'service_type_id' => $serviceType->id,
        'custom_fields' => ['temperature' => '36.5'],
    ]);

    expect($attendance->fresh()->custom_fields)->toBe(['temperature' => '36.5']);
});

test('attendance hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant->id]);

    $attendance = Attendance::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'service_type_id' => $serviceType->id,
    ]);

    expect($attendance->toArray())->not->toHaveKey('tenant_id');
});

test('attendance belongs to member', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant->id]);

    $attendance = Attendance::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'service_type_id' => $serviceType->id,
    ]);

    expect($attendance->member->id)->toBe($member->id);
});

test('attendance belongs to service type', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant->id]);

    $attendance = Attendance::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'service_type_id' => $serviceType->id,
    ]);

    expect($attendance->serviceType->id)->toBe($serviceType->id);
});

test('attendance scope forDate filters correctly', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant->id]);

    Attendance::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'service_type_id' => $serviceType->id,
        'date' => '2026-03-09',
    ]);
    Attendance::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'service_type_id' => $serviceType->id,
        'date' => '2026-03-02',
    ]);

    expect(Attendance::forDate('2026-03-09')->count())->toBe(1);
});

test('attendance scope firstTimers filters correctly', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member1 = Member::factory()->create(['tenant_id' => $tenant->id]);
    $member2 = Member::factory()->create(['tenant_id' => $tenant->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant->id]);

    Attendance::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member1->id,
        'service_type_id' => $serviceType->id,
        'is_first_time' => true,
    ]);
    Attendance::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member2->id,
        'service_type_id' => $serviceType->id,
        'is_first_time' => false,
    ]);

    expect(Attendance::firstTimers()->count())->toBe(1);
});

test('attendance casts date correctly', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant->id]);

    $attendance = Attendance::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'service_type_id' => $serviceType->id,
        'date' => '2026-03-09',
    ]);

    expect($attendance->date)->toBeInstanceOf(Carbon\Carbon::class)
        ->and($attendance->date->toDateString())->toBe('2026-03-09');
});
