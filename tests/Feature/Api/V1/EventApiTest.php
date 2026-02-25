<?php

use App\Models\Event;
use App\Models\Tenant;
use App\Models\User;

test('unauthenticated request to events returns 401', function () {
    $this->getJson('/api/v1/events')
        ->assertUnauthorized();
});

test('authenticated user can list events', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Event::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/events')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'slug', 'start_at', 'end_at', 'location', 'is_upcoming', 'is_past'],
            ],
            'links',
            'meta',
        ]);
});

test('events response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Event::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/events')
        ->assertOk();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('authenticated user can create an event', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/events', [
            'title' => 'Culte de louange',
            'slug' => 'culte-de-louange',
            'start_at' => '2026-03-15T10:00:00',
            'end_at' => '2026-03-15T12:00:00',
            'location' => 'Temple central',
            'capacity' => 300,
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Culte de louange')
        ->assertJsonPath('data.location', 'Temple central');
});

test('authenticated user can view an event', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $event = Event::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/events/{$event->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $event->id);
});

test('authenticated user can update an event', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $event = Event::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/events/{$event->id}", [
            'title' => 'Retraite spirituelle',
            'location' => 'Centre de conférence',
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Retraite spirituelle')
        ->assertJsonPath('data.location', 'Centre de conférence');
});

test('authenticated user can delete an event', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $event = Event::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/events/{$event->id}")
        ->assertNoContent();

    expect(Event::find($event->id))->toBeNull();
});

test('events list is paginated', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Event::factory()->count(20)->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/events?per_page=5')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(5)
        ->and($response->json('meta.last_page'))->toBe(4);
});

test('events can be filtered by upcoming', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Event::factory()->create([
        'tenant_id' => $tenant->id,
        'start_at' => now()->addWeek(),
    ]);
    Event::factory()->create([
        'tenant_id' => $tenant->id,
        'start_at' => now()->subWeek(),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/events?upcoming=true')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

test('events can be filtered by location', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Event::factory()->create([
        'tenant_id' => $tenant->id,
        'location' => 'Temple central de Lomé',
    ]);
    Event::factory()->create([
        'tenant_id' => $tenant->id,
        'location' => 'Salle annexe',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/events?location=Lomé')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

test('event end_at must be after start_at', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/events', [
            'title' => 'Invalid Event',
            'slug' => 'invalid-event',
            'start_at' => '2026-03-15T12:00:00',
            'end_at' => '2026-03-15T10:00:00',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('end_at');
});
