<?php

use App\Models\Devotional;
use App\Models\Tenant;
use App\Models\User;

test('devotional belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $devotional = Devotional::factory()->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    expect(Devotional::find($devotional->id))->toBeNull();
});

test('devotional count is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    Devotional::factory()->count(4)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    Devotional::factory()->count(2)->create(['tenant_id' => $tenant2->id]);

    expect(Devotional::count())->toBe(2);

    tenancy()->initialize($tenant1);
    expect(Devotional::count())->toBe(4);
});

test('API returns only devotionals for authenticated tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    Devotional::factory()->count(3)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    Devotional::factory()->count(5)->create(['tenant_id' => $tenant2->id]);

    tenancy()->initialize($tenant1);

    $response = $this->actingAs($user1, 'sanctum')
        ->getJson('/api/v1/devotionals')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(3);
});
