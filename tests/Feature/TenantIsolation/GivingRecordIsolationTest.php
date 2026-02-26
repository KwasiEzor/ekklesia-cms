<?php

use App\Events\ContentChanged;
use App\Models\GivingRecord;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;

// Fake ContentChanged to prevent the observer/queue from resetting tenant context
beforeEach(function () {
    Event::fake([ContentChanged::class]);
});

test('giving record belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $record = GivingRecord::factory()->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    expect(GivingRecord::find($record->id))->toBeNull();
});

test('giving record count is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    GivingRecord::factory()->count(4)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    GivingRecord::factory()->count(2)->create(['tenant_id' => $tenant2->id]);

    expect(GivingRecord::count())->toBe(2);

    tenancy()->initialize($tenant1);
    expect(GivingRecord::count())->toBe(4);
});

test('API returns only giving records for authenticated tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    GivingRecord::factory()->count(3)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);
    GivingRecord::factory()->count(7)->create(['tenant_id' => $tenant2->id]);

    tenancy()->initialize($tenant1);

    $response = $this->actingAs($user1, 'sanctum')
        ->getJson('/api/v1/giving-records')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(3);
});
