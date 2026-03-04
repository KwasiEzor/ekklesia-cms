<?php

use App\Models\Campus;
use App\Models\Event;
use App\Models\Member;
use App\Models\Tenant;

test('campus casts custom_fields as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $campus = Campus::factory()->create([
        'tenant_id' => $tenant->id,
        'custom_fields' => ['parking' => true],
    ]);

    expect($campus->fresh()->custom_fields)->toBe(['parking' => true]);
});

test('campus casts is_main as boolean', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $campus = Campus::factory()->main()->create([
        'tenant_id' => $tenant->id,
    ]);

    expect($campus->fresh()->is_main)->toBeTrue()
        ->and($campus->fresh()->is_main)->toBeBool();
});

test('campus casts capacity as integer', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $campus = Campus::factory()->create([
        'tenant_id' => $tenant->id,
        'capacity' => 500,
    ]);

    expect($campus->fresh()->capacity)->toBe(500)
        ->and($campus->fresh()->capacity)->toBeInt();
});

test('campus hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $campus = Campus::factory()->create(['tenant_id' => $tenant->id]);
    $array = $campus->toArray();

    expect($array)->not->toHaveKey('tenant_id');
});

test('campus has members relationship', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $campus = Campus::factory()->create(['tenant_id' => $tenant->id]);
    Member::factory()->count(3)->create([
        'tenant_id' => $tenant->id,
        'campus_id' => $campus->id,
    ]);

    expect($campus->members)->toHaveCount(3);
});

test('campus has events relationship', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $campus = Campus::factory()->create(['tenant_id' => $tenant->id]);
    Event::factory()->count(2)->create([
        'tenant_id' => $tenant->id,
        'campus_id' => $campus->id,
    ]);

    expect($campus->events)->toHaveCount(2);
});

test('member belongs to campus', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $campus = Campus::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'campus_id' => $campus->id,
    ]);

    expect($member->campus->id)->toBe($campus->id);
});

test('event can be scoped by campus', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $campus1 = Campus::factory()->create(['tenant_id' => $tenant->id]);
    $campus2 = Campus::factory()->create(['tenant_id' => $tenant->id]);

    Event::factory()->count(3)->create(['tenant_id' => $tenant->id, 'campus_id' => $campus1->id]);
    Event::factory()->count(2)->create(['tenant_id' => $tenant->id, 'campus_id' => $campus2->id]);
    Event::factory()->create(['tenant_id' => $tenant->id, 'campus_id' => null]);

    expect(Event::forCampus($campus1->id)->count())->toBe(3)
        ->and(Event::forCampus($campus2->id)->count())->toBe(2)
        ->and(Event::forCampus(null)->count())->toBe(6);
});
