<?php

use App\Events\ContentChanged;
use App\Models\Event;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event as EventFacade;

// Fake ContentChanged to prevent the observer/queue from resetting tenant context
beforeEach(function () {
    EventFacade::fake([ContentChanged::class]);
});

test('event belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $event = Event::factory()->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    expect(Event::find($event->id))->toBeNull();
});

test('event count is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    Event::factory()->count(4)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    Event::factory()->count(2)->create(['tenant_id' => $tenant2->id]);

    expect(Event::count())->toBe(2);

    tenancy()->initialize($tenant1);
    expect(Event::count())->toBe(4);
});

test('API returns only events for authenticated tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    Event::factory()->count(3)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);
    Event::factory()->count(7)->create(['tenant_id' => $tenant2->id]);

    // Switch back to tenant1 context before making the API call
    tenancy()->initialize($tenant1);

    $response = $this->actingAs($user1, 'sanctum')
        ->getJson('/api/v1/events')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(3);
});
