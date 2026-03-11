<?php

use App\Models\Campaign;
use App\Models\Tenant;
use App\Models\User;

test('campaign belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $campaign = Campaign::factory()->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    expect(Campaign::find($campaign->id))->toBeNull();
});

test('campaign count is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    Campaign::factory()->count(2)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    Campaign::factory()->count(4)->create(['tenant_id' => $tenant2->id]);

    expect(Campaign::count())->toBe(4);

    tenancy()->initialize($tenant1);
    expect(Campaign::count())->toBe(2);
});

test('API returns only campaigns for authenticated tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    Campaign::factory()->count(3)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    Campaign::factory()->count(5)->create(['tenant_id' => $tenant2->id]);

    tenancy()->initialize($tenant1);

    $response = $this->actingAs($user1, 'sanctum')
        ->getJson('/api/v1/campaigns')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(3);
});
