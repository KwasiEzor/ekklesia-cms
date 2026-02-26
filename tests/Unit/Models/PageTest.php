<?php

use App\Models\Page;
use App\Models\Tenant;

test('page casts content_blocks as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $blocks = [
        ['type' => 'heading', 'data' => ['level' => 'h2', 'content' => 'Bienvenue']],
        ['type' => 'rich_text', 'data' => ['body' => 'Lorem ipsum']],
    ];

    $page = Page::factory()->create([
        'tenant_id' => $tenant->id,
        'content_blocks' => $blocks,
    ]);

    expect($page->fresh()->content_blocks)->toEqual($blocks);
});

test('page casts published_at as datetime', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $page = Page::factory()->create([
        'tenant_id' => $tenant->id,
        'published_at' => '2026-03-01 08:00:00',
    ]);

    expect($page->fresh()->published_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('page casts custom_fields as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $page = Page::factory()->create([
        'tenant_id' => $tenant->id,
        'custom_fields' => ['theme' => 'dark'],
    ]);

    expect($page->fresh()->custom_fields)->toBe(['theme' => 'dark']);
});

test('page hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $page = Page::factory()->create(['tenant_id' => $tenant->id]);
    $array = $page->toArray();

    expect($array)->not->toHaveKey('tenant_id');
});

test('page generates slug from title', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $page = Page::factory()->create([
        'tenant_id' => $tenant->id,
        'title' => 'Notre histoire',
        'slug' => null,
    ]);

    expect($page->fresh()->slug)->toBe('notre-histoire');
});

test('page is_published returns true when published_at is in the past', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $page = Page::factory()->create([
        'tenant_id' => $tenant->id,
        'published_at' => now()->subDay(),
    ]);

    expect($page->is_published)->toBeTrue();
});

test('page is_published returns false when published_at is null', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $page = Page::factory()->create([
        'tenant_id' => $tenant->id,
        'published_at' => null,
    ]);

    expect($page->is_published)->toBeFalse();
});

test('page is_published returns false when published_at is in the future', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $page = Page::factory()->create([
        'tenant_id' => $tenant->id,
        'published_at' => now()->addDay(),
    ]);

    expect($page->is_published)->toBeFalse();
});
