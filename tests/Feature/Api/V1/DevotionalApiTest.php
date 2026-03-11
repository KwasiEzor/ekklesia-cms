<?php

use App\Models\Devotional;
use App\Models\DevotionalSeries;
use App\Models\Tenant;
use App\Models\User;

test('unauthenticated request to devotionals returns 401', function () {
    $this->getJson('/api/v1/devotionals')
        ->assertUnauthorized();
});

test('authenticated user can list devotionals', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    Devotional::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/devotionals')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'slug', 'verse_reference', 'reflection', 'scheduled_date', 'status'],
            ],
            'links',
            'meta',
        ]);
});

test('devotionals response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Devotional::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/devotionals')
        ->assertSuccessful();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('authenticated user can create a devotional', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/devotionals', [
            'title' => 'La grâce suffisante',
            'verse_reference' => '2 Corinthiens 12:9',
            'verse_text' => 'Ma grâce te suffit, car ma puissance s\'accomplit dans la faiblesse.',
            'reflection' => 'Réflexion sur la grâce de Dieu dans nos moments de faiblesse.',
            'prayer_point' => 'Seigneur, aide-moi à accepter ta grâce.',
            'scheduled_date' => '2026-03-15',
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'La grâce suffisante')
        ->assertJsonPath('data.verse_reference', '2 Corinthiens 12:9');
});

test('authenticated user can create devotional with series', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $series = DevotionalSeries::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/devotionals', [
            'title' => 'Psaume de louange',
            'series_id' => $series->id,
            'verse_reference' => 'Psaumes 150:6',
            'reflection' => 'Que tout ce qui respire loue l\'Éternel.',
            'scheduled_date' => '2026-03-16',
        ])
        ->assertCreated()
        ->assertJsonPath('data.series.id', $series->id);
});

test('authenticated user can update a devotional', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $devotional = Devotional::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/devotionals/{$devotional->id}", [
            'title' => 'Titre mis à jour',
            'status' => 'published',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'Titre mis à jour')
        ->assertJsonPath('data.status', 'published');
});

test('authenticated user can delete a devotional', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $devotional = Devotional::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/devotionals/{$devotional->id}")
        ->assertNoContent();

    expect(Devotional::find($devotional->id))->toBeNull();
});

test('devotionals can be filtered by status', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    Devotional::factory()->create(['tenant_id' => $tenant->id, 'status' => 'draft']);
    Devotional::factory()->published()->create(['tenant_id' => $tenant->id]);
    Devotional::factory()->published()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/devotionals?status=published')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(2);
});

test('devotionals can be filtered by date', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    Devotional::factory()->create([
        'tenant_id' => $tenant->id,
        'scheduled_date' => '2026-03-15',
    ]);
    Devotional::factory()->create([
        'tenant_id' => $tenant->id,
        'scheduled_date' => '2026-03-16',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/devotionals?date=2026-03-15')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1);
});

test('devotional show includes series data', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $series = DevotionalSeries::factory()->create(['tenant_id' => $tenant->id]);
    $devotional = Devotional::factory()->create([
        'tenant_id' => $tenant->id,
        'series_id' => $series->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/devotionals/{$devotional->id}")
        ->assertSuccessful();

    expect($response->json('data.series.id'))->toBe($series->id);
});
