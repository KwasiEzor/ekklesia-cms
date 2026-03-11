<?php

use App\Models\Member;
use App\Models\Tenant;
use App\Models\Testimony;
use App\Models\User;

test('unauthenticated request to testimonies returns 401', function () {
    $this->getJson('/api/v1/testimonies')
        ->assertUnauthorized();
});

test('authenticated user can list testimonies', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    Testimony::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/testimonies')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'content', 'category', 'status', 'is_anonymous'],
            ],
            'links',
            'meta',
        ]);
});

test('testimonies response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Testimony::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/testimonies')
        ->assertSuccessful();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('anonymous testimony hides member info', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    Testimony::factory()->anonymous()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/testimonies')
        ->assertSuccessful();

    expect($response->json('data.0.is_anonymous'))->toBeTrue()
        ->and($response->json('data.0'))->not->toHaveKey('member');
});

test('authenticated user can create a testimony', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/testimonies', [
            'title' => 'Dieu m\'a guéri',
            'member_id' => $member->id,
            'content' => 'Mon témoignage de guérison miraculeuse après des années de souffrance.',
            'category' => 'healing',
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Dieu m\'a guéri')
        ->assertJsonPath('data.category', 'healing')
        ->assertJsonPath('data.status', 'submitted');
});

test('authenticated user can update a testimony', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $testimony = Testimony::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/testimonies/{$testimony->id}", [
            'status' => 'approved',
            'is_featured' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'approved')
        ->assertJsonPath('data.is_featured', true);
});

test('authenticated user can delete a testimony', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $testimony = Testimony::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/testimonies/{$testimony->id}")
        ->assertNoContent();

    expect(Testimony::find($testimony->id))->toBeNull();
});

test('testimonies can be filtered by status', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    Testimony::factory()->create(['tenant_id' => $tenant->id, 'status' => 'submitted']);
    Testimony::factory()->approved()->create(['tenant_id' => $tenant->id]);
    Testimony::factory()->approved()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/testimonies?status=approved')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(2);
});

test('testimonies can be filtered by category', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    Testimony::factory()->create(['tenant_id' => $tenant->id, 'category' => 'healing']);
    Testimony::factory()->create(['tenant_id' => $tenant->id, 'category' => 'provision']);
    Testimony::factory()->create(['tenant_id' => $tenant->id, 'category' => 'healing']);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/testimonies?category=healing')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(2);
});

test('authenticated user can react to a testimony', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $testimony = Testimony::factory()->approved()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/testimonies/{$testimony->id}/react", [
            'type' => 'amen',
        ])
        ->assertSuccessful()
        ->assertJsonPath('reactions.amen', 1)
        ->assertJsonPath('reaction_count', 1);
});

test('reaction with invalid type returns validation error', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $testimony = Testimony::factory()->approved()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/testimonies/{$testimony->id}/react", [
            'type' => 'invalid',
        ])
        ->assertUnprocessable();
});
