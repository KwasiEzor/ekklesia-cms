<?php

use App\Models\Fund;
use App\Models\Tenant;
use App\Models\User;

test('unauthenticated request to funds returns 401', function () {
    $this->getJson('/api/v1/funds')
        ->assertUnauthorized();
});

test('authenticated user can list funds', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    Fund::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/funds')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'type', 'is_active'],
            ],
            'links',
            'meta',
        ]);
});

test('funds response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Fund::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/funds')
        ->assertSuccessful();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('authenticated user can create a fund', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/funds', [
            'name' => 'Dîmes mensuelles',
            'type' => 'tithe',
            'description' => 'Fonds pour les dîmes',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Dîmes mensuelles')
        ->assertJsonPath('data.type', 'tithe');
});

test('authenticated user can update a fund', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $fund = Fund::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/funds/{$fund->id}", [
            'name' => 'Fonds mis à jour',
            'is_active' => false,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Fonds mis à jour')
        ->assertJsonPath('data.is_active', false);
});

test('authenticated user can delete a fund', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $fund = Fund::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/funds/{$fund->id}")
        ->assertNoContent();

    expect(Fund::find($fund->id))->toBeNull();
});

test('funds can be filtered by type', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    Fund::factory()->ofType('tithe')->create(['tenant_id' => $tenant->id]);
    Fund::factory()->ofType('offering')->create(['tenant_id' => $tenant->id]);
    Fund::factory()->ofType('tithe')->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/funds?type=tithe')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(2);
});

test('funds can be filtered by active status', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    Fund::factory()->create(['tenant_id' => $tenant->id, 'is_active' => true]);
    Fund::factory()->create(['tenant_id' => $tenant->id, 'is_active' => true]);
    Fund::factory()->inactive()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/funds?active=1')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(2);
});

test('fund show returns single fund', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $fund = Fund::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Offrandes spéciales',
        'type' => 'offering',
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/funds/{$fund->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Offrandes spéciales')
        ->assertJsonPath('data.type', 'offering');
});
