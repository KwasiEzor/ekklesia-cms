<?php

use App\Events\ContentChanged;
use App\Models\Announcement;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;

// Fake ContentChanged to prevent the observer/queue from resetting tenant context
beforeEach(function () {
    Event::fake([ContentChanged::class]);
});

test('announcement belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $announcement = Announcement::factory()->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    expect(Announcement::find($announcement->id))->toBeNull();
});

test('announcement count is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    Announcement::factory()->count(4)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    Announcement::factory()->count(2)->create(['tenant_id' => $tenant2->id]);

    expect(Announcement::count())->toBe(2);

    tenancy()->initialize($tenant1);
    expect(Announcement::count())->toBe(4);
});

test('API returns only announcements for authenticated tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    Announcement::factory()->count(3)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);
    Announcement::factory()->count(7)->create(['tenant_id' => $tenant2->id]);

    // Switch back to tenant1 context before making the API call
    tenancy()->initialize($tenant1);

    $response = $this->actingAs($user1, 'sanctum')
        ->getJson('/api/v1/announcements')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(3);
});
