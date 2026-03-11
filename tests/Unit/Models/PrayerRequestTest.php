<?php

use App\Models\Member;
use App\Models\PrayerCommitment;
use App\Models\PrayerRequest;
use App\Models\Tenant;

test('prayer request hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $prayer = PrayerRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
    ]);

    expect($prayer->toArray())->not->toHaveKey('tenant_id');
});

test('prayer request belongs to member', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $prayer = PrayerRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
    ]);

    expect($prayer->member->id)->toBe($member->id);
});

test('prayer request has many commitments', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member1 = Member::factory()->create(['tenant_id' => $tenant->id]);
    $member2 = Member::factory()->create(['tenant_id' => $tenant->id]);
    $prayer = PrayerRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member1->id,
    ]);

    PrayerCommitment::create([
        'prayer_request_id' => $prayer->id,
        'member_id' => $member1->id,
        'tenant_id' => $tenant->id,
        'prayed_at' => now(),
    ]);
    PrayerCommitment::create([
        'prayer_request_id' => $prayer->id,
        'member_id' => $member2->id,
        'tenant_id' => $tenant->id,
        'prayed_at' => now(),
    ]);

    expect($prayer->commitments)->toHaveCount(2)
        ->and($prayer->prayer_count)->toBe(2);
});

test('prayer request scope active works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);

    PrayerRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'status' => 'active',
    ]);
    PrayerRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'status' => 'answered',
    ]);

    expect(PrayerRequest::active()->count())->toBe(1);
});

test('prayer request markAsAnswered updates correctly', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $prayer = PrayerRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
    ]);

    $prayer->markAsAnswered('Dieu a répondu !');

    $fresh = $prayer->fresh();
    expect($fresh->status)->toBe('answered')
        ->and($fresh->answered_at)->not->toBeNull()
        ->and($fresh->testimony)->toBe('Dieu a répondu !');
});

test('prayer request casts booleans correctly', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $prayer = PrayerRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'is_anonymous' => true,
        'is_featured' => true,
    ]);

    expect($prayer->fresh()->is_anonymous)->toBeTrue()
        ->and($prayer->fresh()->is_featured)->toBeTrue();
});
