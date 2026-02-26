<?php

use App\Events\ContentChanged;
use App\Models\Gallery;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake([ContentChanged::class]);
});

test('gallery belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $gallery = Gallery::factory()->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    expect(Gallery::find($gallery->id))->toBeNull();
});

test('gallery count is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    Gallery::factory()->count(3)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    Gallery::factory()->count(2)->create(['tenant_id' => $tenant2->id]);

    expect(Gallery::count())->toBe(2);

    tenancy()->initialize($tenant1);
    expect(Gallery::count())->toBe(3);
});

test('gallery API is scoped to current tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    Gallery::factory()->count(2)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);
    Gallery::factory()->count(1)->create(['tenant_id' => $tenant2->id]);

    $response = $this->actingAs($user2, 'sanctum')
        ->getJson('/api/v1/galleries')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});
