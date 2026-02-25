<?php

use App\Models\Sermon;
use App\Models\Tenant;
use App\Models\User;

test('unauthenticated request returns 401', function () {
    $this->getJson('/api/v1/sermons')
        ->assertUnauthorized();
});

test('authenticated user can list sermons', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Sermon::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/sermons')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'slug', 'speaker', 'date', 'duration', 'tags'],
            ],
            'links',
            'meta',
        ]);
});

test('sermons response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Sermon::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/sermons')
        ->assertOk();

    $firstSermon = $response->json('data.0');
    expect($firstSermon)->not->toHaveKey('tenant_id');
});

test('authenticated user can create a sermon', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/sermons', [
            'title' => 'La puissance de la foi',
            'slug' => 'la-puissance-de-la-foi',
            'speaker' => 'Pasteur Emmanuel',
            'date' => '2026-02-20',
            'duration' => 2700,
            'tags' => ['foi', 'prière'],
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'La puissance de la foi');
});

test('authenticated user can view a sermon', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $sermon = Sermon::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/sermons/{$sermon->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $sermon->id);
});

test('authenticated user can update a sermon', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $sermon = Sermon::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/sermons/{$sermon->id}", [
            'title' => 'Titre mis à jour',
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Titre mis à jour');
});

test('authenticated user can delete a sermon', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $sermon = Sermon::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/sermons/{$sermon->id}")
        ->assertNoContent();

    expect(Sermon::find($sermon->id))->toBeNull();
});

test('sermons list is paginated', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Sermon::factory()->count(20)->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/sermons?per_page=5')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(5)
        ->and($response->json('meta.last_page'))->toBe(4);
});

test('sermons can be filtered by speaker', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Sermon::factory()->create(['tenant_id' => $tenant->id, 'speaker' => 'Pasteur Jean']);
    Sermon::factory()->create(['tenant_id' => $tenant->id, 'speaker' => 'Pasteur Pierre']);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/sermons?speaker=Pasteur+Jean')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.speaker'))->toBe('Pasteur Jean');
});
