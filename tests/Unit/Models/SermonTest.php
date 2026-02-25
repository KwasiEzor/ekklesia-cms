<?php

use App\Models\Sermon;
use App\Models\SermonSeries;
use App\Models\Tenant;

test('sermon casts custom_fields as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $sermon = Sermon::factory()->create([
        'tenant_id' => $tenant->id,
        'custom_fields' => ['key' => 'value'],
    ]);

    expect($sermon->fresh()->custom_fields)->toBe(['key' => 'value']);
});

test('sermon casts tags as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $sermon = Sermon::factory()->create([
        'tenant_id' => $tenant->id,
        'tags' => ['foi', 'prière'],
    ]);

    expect($sermon->fresh()->tags)->toBe(['foi', 'prière']);
});

test('sermon hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $sermon = Sermon::factory()->create(['tenant_id' => $tenant->id]);
    $array = $sermon->toArray();

    expect($array)->not->toHaveKey('tenant_id');
});

test('sermon formatted_duration formats correctly', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $sermon = Sermon::factory()->create([
        'tenant_id' => $tenant->id,
        'duration' => 3661, // 1h 1m 1s
    ]);

    expect($sermon->formatted_duration)->toBe('1:01:01');
});

test('sermon formatted_duration handles minutes only', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $sermon = Sermon::factory()->create([
        'tenant_id' => $tenant->id,
        'duration' => 1830, // 30m 30s
    ]);

    expect($sermon->formatted_duration)->toBe('30:30');
});

test('sermon formatted_duration returns null when no duration', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $sermon = Sermon::factory()->create([
        'tenant_id' => $tenant->id,
        'duration' => null,
    ]);

    expect($sermon->formatted_duration)->toBeNull();
});

test('sermon belongs to series', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $series = SermonSeries::factory()->create(['tenant_id' => $tenant->id]);
    $sermon = Sermon::factory()->create([
        'tenant_id' => $tenant->id,
        'series_id' => $series->id,
    ]);

    expect($sermon->series->id)->toBe($series->id);
});
