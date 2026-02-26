<?php

use App\Models\CellGroup;
use App\Models\Member;
use App\Models\Tenant;

test('member casts custom_fields as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'custom_fields' => ['ministry' => 'worship'],
    ]);

    expect($member->fresh()->custom_fields)->toBe(['ministry' => 'worship']);
});

test('member casts baptism_date as date', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'baptism_date' => '2020-06-15',
    ]);

    expect($member->fresh()->baptism_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('member casts previous_version as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'previous_version' => ['first_name' => 'Jean'],
    ]);

    expect($member->fresh()->previous_version)->toBe(['first_name' => 'Jean']);
});

test('member hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $array = $member->toArray();

    expect($array)->not->toHaveKey('tenant_id');
});

test('member full_name accessor returns first and last name', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'first_name' => 'Jean',
        'last_name' => 'Dupont',
    ]);

    expect($member->full_name)->toBe('Jean Dupont');
});

test('member name accessor aliases full_name', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'first_name' => 'Marie',
        'last_name' => 'Kone',
    ]);

    expect($member->name)->toBe('Marie Kone')
        ->and($member->name)->toBe($member->full_name);
});

test('member belongs to cell group', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $cellGroup = CellGroup::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'cell_group_id' => $cellGroup->id,
    ]);

    expect($member->cellGroup)->toBeInstanceOf(CellGroup::class)
        ->and($member->cellGroup->id)->toBe($cellGroup->id);
});

test('member cell_group_id is nullable', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'cell_group_id' => null,
    ]);

    expect($member->cellGroup)->toBeNull();
});
