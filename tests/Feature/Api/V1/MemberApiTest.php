<?php

use App\Events\ContentChanged;
use App\Models\CellGroup;
use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;

// Fake ContentChanged to prevent the observer from resetting tenant context
beforeEach(function () {
    Event::fake([ContentChanged::class]);
});

test('unauthenticated request to members returns 401', function () {
    $this->getJson('/api/v1/members')
        ->assertUnauthorized();
});

test('authenticated user can list members', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Member::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/members')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'first_name', 'last_name', 'full_name', 'email', 'phone', 'baptism_date', 'cell_group_id', 'status'],
            ],
            'links',
            'meta',
        ]);
});

test('members response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Member::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/members')
        ->assertOk();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('authenticated user can create a member', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/members', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@example.com',
            'phone' => '+225 07 00 00 00',
            'status' => 'active',
        ])
        ->assertCreated()
        ->assertJsonPath('data.first_name', 'Jean')
        ->assertJsonPath('data.last_name', 'Dupont')
        ->assertJsonPath('data.full_name', 'Jean Dupont');
});

test('authenticated user can view a member', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/members/{$member->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $member->id);
});

test('authenticated user can update a member', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/members/{$member->id}", [
            'first_name' => 'Pierre',
            'status' => 'inactive',
        ])
        ->assertOk()
        ->assertJsonPath('data.first_name', 'Pierre')
        ->assertJsonPath('data.status', 'inactive');
});

test('authenticated user can delete a member', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/members/{$member->id}")
        ->assertNoContent();

    expect(Member::find($member->id))->toBeNull();
});

test('members list is paginated', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Member::factory()->count(20)->create(['tenant_id' => $tenant->id, 'email' => null]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/members?per_page=5')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(5)
        ->and($response->json('meta.last_page'))->toBe(4);
});

test('members can be filtered by status', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Member::factory()->active()->count(3)->create(['tenant_id' => $tenant->id, 'email' => null]);
    Member::factory()->inactive()->count(2)->create(['tenant_id' => $tenant->id, 'email' => null]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/members?status=active')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(3);
});

test('members can be filtered by cell_group_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $cellGroup = CellGroup::factory()->create(['tenant_id' => $tenant->id]);
    Member::factory()->count(2)->create(['tenant_id' => $tenant->id, 'cell_group_id' => $cellGroup->id, 'email' => null]);
    Member::factory()->count(3)->create(['tenant_id' => $tenant->id, 'cell_group_id' => null, 'email' => null]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/members?cell_group_id={$cellGroup->id}")
        ->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

test('members can be searched by name or email', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Member::factory()->create(['tenant_id' => $tenant->id, 'first_name' => 'Jean', 'last_name' => 'Dupont', 'email' => 'jean@example.com']);
    Member::factory()->create(['tenant_id' => $tenant->id, 'first_name' => 'Marie', 'last_name' => 'Kone', 'email' => 'marie@example.com']);
    Member::factory()->create(['tenant_id' => $tenant->id, 'first_name' => 'Paul', 'last_name' => 'Bamba', 'email' => 'paul@example.com']);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/members?search=Jean')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.first_name'))->toBe('Jean');
});

test('member email must be unique per tenant', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Member::factory()->create(['tenant_id' => $tenant->id, 'email' => 'duplicate@example.com']);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/members', [
            'first_name' => 'Autre',
            'last_name' => 'Personne',
            'email' => 'duplicate@example.com',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});
