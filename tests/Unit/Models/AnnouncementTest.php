<?php

use App\Models\Announcement;
use App\Models\Tenant;

test('announcement casts custom_fields as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $announcement = Announcement::factory()->create([
        'tenant_id' => $tenant->id,
        'custom_fields' => ['priority' => 'high'],
    ]);

    expect($announcement->fresh()->custom_fields)->toBe(['priority' => 'high']);
});

test('announcement casts published_at and expires_at as datetime', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $announcement = Announcement::factory()->create([
        'tenant_id' => $tenant->id,
        'published_at' => '2026-03-01 08:00:00',
        'expires_at' => '2026-03-31 23:59:59',
    ]);

    $fresh = $announcement->fresh();
    expect($fresh->published_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($fresh->expires_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('announcement casts pinned as boolean', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $announcement = Announcement::factory()->create([
        'tenant_id' => $tenant->id,
        'pinned' => true,
    ]);

    expect($announcement->fresh()->pinned)->toBeTrue()
        ->and($announcement->fresh()->pinned)->toBeBool();
});

test('announcement hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $announcement = Announcement::factory()->create(['tenant_id' => $tenant->id]);
    $array = $announcement->toArray();

    expect($array)->not->toHaveKey('tenant_id');
});

test('announcement is_active returns true when published and not expired', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $announcement = Announcement::factory()->create([
        'tenant_id' => $tenant->id,
        'published_at' => now()->subDay(),
        'expires_at' => now()->addWeek(),
    ]);

    expect($announcement->is_active)->toBeTrue()
        ->and($announcement->is_expired)->toBeFalse();
});

test('announcement is_active returns true when published with no expiry', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $announcement = Announcement::factory()->create([
        'tenant_id' => $tenant->id,
        'published_at' => now()->subDay(),
        'expires_at' => null,
    ]);

    expect($announcement->is_active)->toBeTrue();
});

test('announcement is_active returns false when not yet published', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $announcement = Announcement::factory()->create([
        'tenant_id' => $tenant->id,
        'published_at' => now()->addDay(),
        'expires_at' => null,
    ]);

    expect($announcement->is_active)->toBeFalse();
});

test('announcement is_expired returns true when past expiry', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $announcement = Announcement::factory()->create([
        'tenant_id' => $tenant->id,
        'published_at' => now()->subMonth(),
        'expires_at' => now()->subDay(),
    ]);

    expect($announcement->is_expired)->toBeTrue()
        ->and($announcement->is_active)->toBeFalse();
});

test('announcement is_expired returns false when no expiry set', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $announcement = Announcement::factory()->create([
        'tenant_id' => $tenant->id,
        'published_at' => now()->subDay(),
        'expires_at' => null,
    ]);

    expect($announcement->is_expired)->toBeFalse();
});
