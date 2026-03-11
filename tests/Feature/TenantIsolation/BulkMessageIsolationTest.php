<?php

use App\Models\BulkMessage;
use App\Models\Tenant;
use App\Models\User;

test('bulk message belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $message = BulkMessage::factory()->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    expect(BulkMessage::find($message->id))->toBeNull();
});

test('bulk message count is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    BulkMessage::factory()->count(3)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    BulkMessage::factory()->count(5)->create(['tenant_id' => $tenant2->id]);

    expect(BulkMessage::count())->toBe(5);

    tenancy()->initialize($tenant1);
    expect(BulkMessage::count())->toBe(3);
});

test('API returns only bulk messages for authenticated tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    BulkMessage::factory()->count(2)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    BulkMessage::factory()->count(4)->create(['tenant_id' => $tenant2->id]);

    tenancy()->initialize($tenant1);

    $response = $this->actingAs($user1, 'sanctum')
        ->getJson('/api/v1/bulk-messages')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(2);
});
