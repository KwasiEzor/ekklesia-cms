<?php

use App\Models\Campaign;
use App\Models\Fund;
use App\Models\Tenant;
use App\Models\User;

test('unauthenticated request to campaigns returns 401', function () {
    $this->getJson('/api/v1/campaigns')
        ->assertUnauthorized();
});

test('authenticated user can list campaigns', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    Campaign::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/campaigns')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'goal_amount', 'currency', 'status', 'start_date'],
            ],
            'links',
            'meta',
        ]);
});

test('campaigns response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Campaign::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/campaigns')
        ->assertSuccessful();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('authenticated user can create a campaign', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/campaigns', [
            'name' => 'Construction du temple',
            'description' => 'Campagne pour construire le nouveau temple',
            'goal_amount' => 5000000,
            'currency' => 'XOF',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Construction du temple')
        ->assertJsonPath('data.goal_amount', '5000000.00');
});

test('authenticated user can create campaign with fund', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $fund = Fund::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/campaigns', [
            'name' => 'Mission été 2026',
            'fund_id' => $fund->id,
            'goal_amount' => 2000000,
            'currency' => 'XOF',
            'start_date' => '2026-06-01',
        ])
        ->assertCreated()
        ->assertJsonPath('data.fund.id', $fund->id);
});

test('authenticated user can update a campaign', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $campaign = Campaign::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/campaigns/{$campaign->id}", [
            'name' => 'Campagne mise à jour',
            'status' => 'completed',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Campagne mise à jour')
        ->assertJsonPath('data.status', 'completed');
});

test('authenticated user can delete a campaign', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $campaign = Campaign::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/campaigns/{$campaign->id}")
        ->assertNoContent();

    expect(Campaign::find($campaign->id))->toBeNull();
});

test('campaigns can be filtered by status', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    Campaign::factory()->create(['tenant_id' => $tenant->id, 'status' => 'active']);
    Campaign::factory()->completed()->create(['tenant_id' => $tenant->id]);
    Campaign::factory()->draft()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/campaigns?status=active')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1);
});

test('campaigns can be filtered by fund', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $fund = Fund::factory()->create(['tenant_id' => $tenant->id]);

    Campaign::factory()->create(['tenant_id' => $tenant->id, 'fund_id' => $fund->id]);
    Campaign::factory()->create(['tenant_id' => $tenant->id, 'fund_id' => $fund->id]);
    Campaign::factory()->create(['tenant_id' => $tenant->id, 'fund_id' => null]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/campaigns?fund_id={$fund->id}")
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(2);
});

test('campaign show includes progress data', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $campaign = Campaign::factory()->create([
        'tenant_id' => $tenant->id,
        'goal_amount' => 1000000,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/campaigns/{$campaign->id}")
        ->assertSuccessful();

    expect($response->json('data'))
        ->toHaveKey('raised_amount')
        ->toHaveKey('progress_percent')
        ->toHaveKey('donor_count')
        ->toHaveKey('is_overdue');
});
