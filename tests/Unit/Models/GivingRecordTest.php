<?php

use App\Models\GivingRecord;
use App\Models\Member;
use App\Models\Tenant;

test('giving record casts amount as decimal', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $record = GivingRecord::factory()->create([
        'tenant_id' => $tenant->id,
        'amount' => 25000.50,
    ]);

    expect($record->fresh()->amount)->toBe('25000.50');
});

test('giving record casts date as date', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $record = GivingRecord::factory()->create([
        'tenant_id' => $tenant->id,
        'date' => '2026-03-01',
    ]);

    expect($record->fresh()->date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('giving record casts custom_fields as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $record = GivingRecord::factory()->create([
        'tenant_id' => $tenant->id,
        'custom_fields' => ['category' => 'tithe'],
    ]);

    expect($record->fresh()->custom_fields)->toBe(['category' => 'tithe']);
});

test('giving record hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $record = GivingRecord::factory()->create(['tenant_id' => $tenant->id]);
    $array = $record->toArray();

    expect($array)->not->toHaveKey('tenant_id');
});

test('giving record belongs to member', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $record = GivingRecord::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
    ]);

    expect($record->member)->toBeInstanceOf(Member::class)
        ->and($record->member->id)->toBe($member->id);
});

test('giving record member_id is nullable for anonymous giving', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $record = GivingRecord::factory()->anonymous()->create(['tenant_id' => $tenant->id]);

    expect($record->member_id)->toBeNull()
        ->and($record->member)->toBeNull();
});

test('giving record is_anonymous returns true when no member', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $record = GivingRecord::factory()->anonymous()->create(['tenant_id' => $tenant->id]);

    expect($record->is_anonymous)->toBeTrue();
});

test('giving record is_anonymous returns false when member is set', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $record = GivingRecord::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
    ]);

    expect($record->is_anonymous)->toBeFalse();
});

test('giving record formatted_amount includes currency', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $record = GivingRecord::factory()->create([
        'tenant_id' => $tenant->id,
        'amount' => 50000,
        'currency' => 'XOF',
    ]);

    expect($record->formatted_amount)->toBe('50 000,00 XOF');
});
