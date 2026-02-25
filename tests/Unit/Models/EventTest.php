<?php

use App\Models\Event;
use App\Models\Tenant;

test('event casts custom_fields as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $event = Event::factory()->create([
        'tenant_id' => $tenant->id,
        'custom_fields' => ['theme' => 'worship'],
    ]);

    expect($event->fresh()->custom_fields)->toBe(['theme' => 'worship']);
});

test('event casts start_at and end_at as datetime', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $event = Event::factory()->create([
        'tenant_id' => $tenant->id,
        'start_at' => '2026-03-15 10:00:00',
        'end_at' => '2026-03-15 12:00:00',
    ]);

    $fresh = $event->fresh();
    expect($fresh->start_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($fresh->end_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('event hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $event = Event::factory()->create(['tenant_id' => $tenant->id]);
    $array = $event->toArray();

    expect($array)->not->toHaveKey('tenant_id');
});

test('event is_upcoming returns true for future events', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $event = Event::factory()->create([
        'tenant_id' => $tenant->id,
        'start_at' => now()->addWeek(),
        'end_at' => now()->addWeek()->addHours(2),
    ]);

    expect($event->is_upcoming)->toBeTrue()
        ->and($event->is_past)->toBeFalse();
});

test('event is_past returns true for past events', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $event = Event::factory()->create([
        'tenant_id' => $tenant->id,
        'start_at' => now()->subWeek(),
        'end_at' => now()->subWeek()->addHours(2),
    ]);

    expect($event->is_past)->toBeTrue()
        ->and($event->is_upcoming)->toBeFalse();
});

test('event casts capacity as integer', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $event = Event::factory()->create([
        'tenant_id' => $tenant->id,
        'capacity' => 200,
    ]);

    expect($event->fresh()->capacity)->toBe(200)
        ->and($event->fresh()->capacity)->toBeInt();
});
