<?php

use App\Models\Campus;
use App\Models\Event;
use App\Models\Tenant;
use App\Models\User;

test('unauthenticated request to campuses returns 401', function () {
    $this->getJson('/api/v1/campuses')
        ->assertUnauthorized();
});

test('authenticated user can list campuses', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Campus::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAsSuperAdmin($user, $tenant)
        ->getJson('/api/v1/campuses')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'city', 'country', 'pastor_name', 'capacity', 'is_main'],
            ],
            'links',
            'meta',
        ]);
});

test('campuses response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Campus::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAsSuperAdmin($user, $tenant)
        ->getJson('/api/v1/campuses')
        ->assertOk();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('authenticated user can create a campus', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAsSuperAdmin($user, $tenant)
        ->postJson('/api/v1/campuses', [
            'name' => 'Campus Nord',
            'slug' => 'campus-nord',
            'city' => 'Lomé',
            'country' => 'Togo',
            'pastor_name' => 'Pasteur Jean',
            'capacity' => 500,
            'is_main' => true,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Campus Nord')
        ->assertJsonPath('data.city', 'Lomé')
        ->assertJsonPath('data.is_main', true);
});

test('authenticated user can view a campus', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $campus = Campus::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAsSuperAdmin($user, $tenant)
        ->getJson("/api/v1/campuses/{$campus->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $campus->id);
});

test('authenticated user can update a campus', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $campus = Campus::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAsSuperAdmin($user, $tenant)
        ->putJson("/api/v1/campuses/{$campus->id}", [
            'name' => 'Campus Sud',
            'city' => 'Kpalimé',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Campus Sud')
        ->assertJsonPath('data.city', 'Kpalimé');
});

test('authenticated user can delete a campus', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $campus = Campus::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAsSuperAdmin($user, $tenant)
        ->deleteJson("/api/v1/campuses/{$campus->id}")
        ->assertNoContent();

    expect(Campus::find($campus->id))->toBeNull();
});

test('campuses list is paginated', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Campus::factory()->count(20)->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAsSuperAdmin($user, $tenant)
        ->getJson('/api/v1/campuses?per_page=5')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(5)
        ->and($response->json('meta.last_page'))->toBe(4);
});

test('campuses can be filtered by city', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Campus::factory()->create(['tenant_id' => $tenant->id, 'city' => 'Lomé']);
    Campus::factory()->create(['tenant_id' => $tenant->id, 'city' => 'Kpalimé']);

    $response = $this->actingAsSuperAdmin($user, $tenant)
        ->getJson('/api/v1/campuses?city=Lomé')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

test('campuses can be filtered by is_main', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Campus::factory()->main()->create(['tenant_id' => $tenant->id]);
    Campus::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAsSuperAdmin($user, $tenant)
        ->getJson('/api/v1/campuses?is_main=true')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

test('events can be filtered by campus_id', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $campus = Campus::factory()->create(['tenant_id' => $tenant->id]);

    Event::factory()->count(2)->create(['tenant_id' => $tenant->id, 'campus_id' => $campus->id]);
    Event::factory()->create(['tenant_id' => $tenant->id, 'campus_id' => null]);

    $response = $this->actingAsSuperAdmin($user, $tenant)
        ->getJson("/api/v1/events?campus_id={$campus->id}")
        ->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

test('campus slug must be unique per tenant', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Campus::factory()->create(['tenant_id' => $tenant->id, 'slug' => 'campus-nord']);

    $this->actingAsSuperAdmin($user, $tenant)
        ->postJson('/api/v1/campuses', [
            'name' => 'Autre Campus Nord',
            'slug' => 'campus-nord',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('slug');
});
