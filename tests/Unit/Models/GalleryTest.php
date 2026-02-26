<?php

use App\Models\Event;
use App\Models\Gallery;
use App\Models\Member;
use App\Models\Tenant;

test('gallery casts custom_fields as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $gallery = Gallery::factory()->create([
        'tenant_id' => $tenant->id,
        'custom_fields' => ['theme' => 'baptême'],
    ]);

    expect($gallery->fresh()->custom_fields)->toBe(['theme' => 'baptême']);
});

test('gallery hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $gallery = Gallery::factory()->create(['tenant_id' => $tenant->id]);
    $array = $gallery->toArray();

    expect($array)->not->toHaveKey('tenant_id');
});

test('gallery generates slug from title', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $gallery = Gallery::factory()->create([
        'tenant_id' => $tenant->id,
        'title' => 'Baptêmes de Pâques 2026',
    ]);

    expect($gallery->slug)->toBe('baptemes-de-paques-2026');
});

test('gallery can be linked to an event via morphTo', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $event = Event::factory()->create(['tenant_id' => $tenant->id]);
    $gallery = Gallery::factory()->create([
        'tenant_id' => $tenant->id,
        'galleryable_type' => Event::class,
        'galleryable_id' => $event->id,
    ]);

    expect($gallery->galleryable)->toBeInstanceOf(Event::class);
    expect($gallery->galleryable->id)->toBe($event->id);
});

test('gallery can be linked to a member via morphTo', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $gallery = Gallery::factory()->create([
        'tenant_id' => $tenant->id,
        'galleryable_type' => Member::class,
        'galleryable_id' => $member->id,
    ]);

    expect($gallery->galleryable)->toBeInstanceOf(Member::class);
    expect($gallery->galleryable->id)->toBe($member->id);
});

test('gallery can exist without a galleryable (standalone)', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $gallery = Gallery::factory()->create([
        'tenant_id' => $tenant->id,
        'galleryable_type' => null,
        'galleryable_id' => null,
    ]);

    expect($gallery->galleryable)->toBeNull();
});

test('event has many galleries', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $event = Event::factory()->create(['tenant_id' => $tenant->id]);
    Gallery::factory()->count(3)->create([
        'tenant_id' => $tenant->id,
        'galleryable_type' => Event::class,
        'galleryable_id' => $event->id,
    ]);

    expect($event->galleries)->toHaveCount(3);
});

test('member has many galleries', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    Gallery::factory()->count(2)->create([
        'tenant_id' => $tenant->id,
        'galleryable_type' => Member::class,
        'galleryable_id' => $member->id,
    ]);

    expect($member->galleries)->toHaveCount(2);
});

test('gallery photo_count returns zero when no media', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $gallery = Gallery::factory()->create(['tenant_id' => $tenant->id]);

    expect($gallery->photo_count)->toBe(0);
});

test('gallery cover_url returns null when no media', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $gallery = Gallery::factory()->create(['tenant_id' => $tenant->id]);

    expect($gallery->cover_url)->toBeNull();
});
