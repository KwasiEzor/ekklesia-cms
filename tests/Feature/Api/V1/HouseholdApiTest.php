<?php

use App\Models\Household;
use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;

test('unauthenticated request to households returns 401', function () {
    $this->getJson('/api/v1/households')
        ->assertUnauthorized();
});

test('authenticated user can list households', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Household::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/households')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'address', 'city', 'phone'],
            ],
            'links',
            'meta',
        ]);
});

test('households response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Household::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/households')
        ->assertOk();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('authenticated user can create a household', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/households', [
            'name' => 'Famille Mbarga',
            'address' => 'Quartier Bastos',
            'city' => 'Yaoundé',
            'phone' => '+237 699 123 456',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Famille Mbarga')
        ->assertJsonPath('data.city', 'Yaoundé');
});

test('authenticated user can view a household with members', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $household = Household::factory()->create(['tenant_id' => $tenant->id]);
    Member::factory()->count(2)->create([
        'tenant_id' => $tenant->id,
        'household_id' => $household->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/households/{$household->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $household->id);

    expect($response->json('data.members'))->toHaveCount(2);
});

test('authenticated user can update a household', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $household = Household::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/households/{$household->id}", [
            'city' => 'Douala',
        ])
        ->assertOk()
        ->assertJsonPath('data.city', 'Douala');
});

test('authenticated user can delete a household', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $household = Household::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/households/{$household->id}")
        ->assertNoContent();

    expect(Household::find($household->id))->toBeNull();
});

test('deleting household does not delete members', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $household = Household::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'household_id' => $household->id,
    ]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/households/{$household->id}")
        ->assertNoContent();

    expect(Member::find($member->id))->not->toBeNull()
        ->and(Member::find($member->id)->household_id)->toBeNull();
});

test('households can be searched by name', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Household::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Famille Mbarga']);
    Household::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Famille Onana']);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/households?search=Mbarga')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});
