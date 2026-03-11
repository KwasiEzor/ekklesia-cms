<?php

use App\Events\ContentChanged;
use App\Models\Household;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake([ContentChanged::class]);
});

test('household belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $household = Household::factory()->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    expect(Household::find($household->id))->toBeNull();
});

test('household count is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    Household::factory()->count(3)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    Household::factory()->count(5)->create(['tenant_id' => $tenant2->id]);

    expect(Household::count())->toBe(5);

    tenancy()->initialize($tenant1);
    expect(Household::count())->toBe(3);
});

test('API returns only households for authenticated tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    Household::factory()->count(2)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    Household::factory()->count(6)->create(['tenant_id' => $tenant2->id]);

    tenancy()->initialize($tenant1);

    $response = $this->actingAs($user1, 'sanctum')
        ->getJson('/api/v1/households')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});
