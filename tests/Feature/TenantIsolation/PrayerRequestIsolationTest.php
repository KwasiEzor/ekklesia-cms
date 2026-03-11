<?php

use App\Events\ContentChanged;
use App\Models\Member;
use App\Models\PrayerRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake([ContentChanged::class]);
});

test('prayer request belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $member = Member::factory()->create(['tenant_id' => $tenant1->id]);
    $prayer = PrayerRequest::factory()->create([
        'tenant_id' => $tenant1->id,
        'member_id' => $member->id,
    ]);

    tenancy()->initialize($tenant2);
    expect(PrayerRequest::find($prayer->id))->toBeNull();
});

test('prayer request count is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $member1 = Member::factory()->create(['tenant_id' => $tenant1->id]);
    PrayerRequest::factory()->count(3)->create([
        'tenant_id' => $tenant1->id,
        'member_id' => $member1->id,
    ]);

    tenancy()->initialize($tenant2);
    $member2 = Member::factory()->create(['tenant_id' => $tenant2->id]);
    PrayerRequest::factory()->count(5)->create([
        'tenant_id' => $tenant2->id,
        'member_id' => $member2->id,
    ]);

    expect(PrayerRequest::count())->toBe(5);

    tenancy()->initialize($tenant1);
    expect(PrayerRequest::count())->toBe(3);
});

test('API returns only prayer requests for authenticated tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    $member1 = Member::factory()->create(['tenant_id' => $tenant1->id]);
    PrayerRequest::factory()->count(2)->create([
        'tenant_id' => $tenant1->id,
        'member_id' => $member1->id,
    ]);

    tenancy()->initialize($tenant2);
    $member2 = Member::factory()->create(['tenant_id' => $tenant2->id]);
    PrayerRequest::factory()->count(4)->create([
        'tenant_id' => $tenant2->id,
        'member_id' => $member2->id,
    ]);

    tenancy()->initialize($tenant1);

    $response = $this->actingAs($user1, 'sanctum')
        ->getJson('/api/v1/prayer-requests')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});
