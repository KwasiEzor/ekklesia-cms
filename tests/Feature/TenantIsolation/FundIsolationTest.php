<?php

use App\Models\Fund;
use App\Models\Tenant;
use App\Models\User;

test('fund belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $fund = Fund::factory()->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    expect(Fund::find($fund->id))->toBeNull();
});

test('fund count is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    Fund::factory()->count(3)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    Fund::factory()->count(5)->create(['tenant_id' => $tenant2->id]);

    expect(Fund::count())->toBe(5);

    tenancy()->initialize($tenant1);
    expect(Fund::count())->toBe(3);
});

test('API returns only funds for authenticated tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    Fund::factory()->count(2)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    Fund::factory()->count(4)->create(['tenant_id' => $tenant2->id]);

    tenancy()->initialize($tenant1);

    $response = $this->actingAs($user1, 'sanctum')
        ->getJson('/api/v1/funds')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(2);
});
