<?php

use App\Models\Devotional;
use App\Models\DevotionalSeries;
use App\Models\Tenant;

test('devotional hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $devotional = Devotional::factory()->create(['tenant_id' => $tenant->id]);

    expect($devotional->toArray())->not->toHaveKey('tenant_id');
});

test('devotional belongs to series', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $series = DevotionalSeries::factory()->create(['tenant_id' => $tenant->id]);
    $devotional = Devotional::factory()->create([
        'tenant_id' => $tenant->id,
        'series_id' => $series->id,
    ]);

    expect($devotional->series->id)->toBe($series->id);
});

test('devotional scope draft works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Devotional::factory()->create(['tenant_id' => $tenant->id, 'status' => 'draft']);
    Devotional::factory()->published()->create(['tenant_id' => $tenant->id]);

    expect(Devotional::draft()->count())->toBe(1);
});

test('devotional scope published works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Devotional::factory()->create(['tenant_id' => $tenant->id, 'status' => 'draft']);
    Devotional::factory()->published()->create(['tenant_id' => $tenant->id]);
    Devotional::factory()->published()->create(['tenant_id' => $tenant->id]);

    expect(Devotional::published()->count())->toBe(2);
});

test('devotional scope forDate works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $targetDate = '2026-03-15';
    Devotional::factory()->create([
        'tenant_id' => $tenant->id,
        'scheduled_date' => $targetDate,
    ]);
    Devotional::factory()->create([
        'tenant_id' => $tenant->id,
        'scheduled_date' => '2026-03-16',
    ]);

    expect(Devotional::forDate($targetDate)->count())->toBe(1);
});

test('devotional publish method works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $devotional = Devotional::factory()->create([
        'tenant_id' => $tenant->id,
        'status' => 'draft',
    ]);

    $devotional->publish();

    $fresh = $devotional->fresh();
    expect($fresh->status)->toBe('published')
        ->and($fresh->published_at)->not->toBeNull()
        ->and($fresh->is_published)->toBeTrue();
});

test('devotional casts dates correctly', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $devotional = Devotional::factory()->create([
        'tenant_id' => $tenant->id,
        'scheduled_date' => '2026-03-15',
    ]);

    expect($devotional->fresh()->scheduled_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('devotional series has many devotionals', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $series = DevotionalSeries::factory()->create(['tenant_id' => $tenant->id]);
    Devotional::factory()->count(3)->create([
        'tenant_id' => $tenant->id,
        'series_id' => $series->id,
    ]);

    expect($series->devotionals)->toHaveCount(3);
});

test('devotional series scope active works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    DevotionalSeries::factory()->create(['tenant_id' => $tenant->id, 'is_active' => true]);
    DevotionalSeries::factory()->inactive()->create(['tenant_id' => $tenant->id]);

    expect(DevotionalSeries::active()->count())->toBe(1);
});
