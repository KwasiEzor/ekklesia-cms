<?php

use App\Models\Tenant;
use App\Models\Testimony;
use App\Models\User;

test('testimony belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $testimony = Testimony::factory()->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    expect(Testimony::find($testimony->id))->toBeNull();
});

test('testimony count is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    Testimony::factory()->count(3)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    Testimony::factory()->count(5)->create(['tenant_id' => $tenant2->id]);

    expect(Testimony::count())->toBe(5);

    tenancy()->initialize($tenant1);
    expect(Testimony::count())->toBe(3);
});

test('API returns only testimonies for authenticated tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    Testimony::factory()->count(2)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    Testimony::factory()->count(4)->create(['tenant_id' => $tenant2->id]);

    tenancy()->initialize($tenant1);

    $response = $this->actingAs($user1, 'sanctum')
        ->getJson('/api/v1/testimonies')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(2);
});
