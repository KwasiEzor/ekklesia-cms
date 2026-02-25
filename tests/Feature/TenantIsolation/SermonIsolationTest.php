<?php

use App\Models\Sermon;
use App\Models\Tenant;
use App\Models\User;

test('sermon belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $sermon = Sermon::factory()->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    expect(Sermon::find($sermon->id))->toBeNull();
});

test('sermon count is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    Sermon::factory()->count(3)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    Sermon::factory()->count(1)->create(['tenant_id' => $tenant2->id]);

    expect(Sermon::count())->toBe(1);

    tenancy()->initialize($tenant1);
    expect(Sermon::count())->toBe(3);
});

test('API returns only sermons for authenticated tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    Sermon::factory()->count(2)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);
    Sermon::factory()->count(5)->create(['tenant_id' => $tenant2->id]);

    // User from tenant2 should only see tenant2 sermons
    $response = $this->actingAs($user2, 'sanctum')
        ->getJson('/api/v1/sermons')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(5);
});
