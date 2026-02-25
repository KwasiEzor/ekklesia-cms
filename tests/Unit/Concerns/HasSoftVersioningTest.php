<?php

use App\Models\Sermon;
use App\Models\Tenant;

test('soft versioning snapshots changed fields on update', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $sermon = Sermon::factory()->create([
        'tenant_id' => $tenant->id,
        'title' => 'Original Title',
        'speaker' => 'Pastor Jean',
    ]);

    $sermon->update(['title' => 'Updated Title']);

    $sermon->refresh();
    expect($sermon->previous_version)->toHaveKey('title', 'Original Title')
        ->and($sermon->previous_version)->toHaveKey('_versioned_at')
        ->and($sermon->previous_version)->not->toHaveKey('speaker');
});

test('revert restores previous version', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $sermon = Sermon::factory()->create([
        'tenant_id' => $tenant->id,
        'title' => 'Original Title',
    ]);

    $sermon->update(['title' => 'Updated Title']);
    $sermon->refresh();

    expect($sermon->title)->toBe('Updated Title');

    $sermon->revertToPreviousVersion();
    $sermon->refresh();

    expect($sermon->title)->toBe('Original Title')
        ->and($sermon->previous_version)->toBeNull();
});

test('revert returns false when no previous version exists', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $sermon = Sermon::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    expect($sermon->revertToPreviousVersion())->toBeFalse();
});

test('hasPreviousVersion returns correct boolean', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $sermon = Sermon::factory()->create([
        'tenant_id' => $tenant->id,
        'title' => 'Original',
    ]);

    expect($sermon->hasPreviousVersion())->toBeFalse();

    $sermon->update(['title' => 'Changed']);
    $sermon->refresh();

    expect($sermon->hasPreviousVersion())->toBeTrue();
});
