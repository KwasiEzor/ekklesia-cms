<?php

use App\Models\Announcement;
use App\Models\Tenant;
use App\Models\User;

test('unauthenticated request to announcements returns 401', function () {
    $this->getJson('/api/v1/announcements')
        ->assertUnauthorized();
});

test('authenticated user can list announcements', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Announcement::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/announcements')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'slug', 'body', 'published_at', 'expires_at', 'pinned', 'target_group', 'is_active', 'is_expired'],
            ],
            'links',
            'meta',
        ]);
});

test('announcements response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Announcement::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/announcements')
        ->assertOk();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('authenticated user can create an announcement', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/announcements', [
            'title' => 'Veillée de prière',
            'slug' => 'veillee-de-priere',
            'body' => 'Tous les membres sont invités à la veillée de prière.',
            'published_at' => '2026-03-01T08:00:00',
            'pinned' => true,
            'target_group' => 'all',
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Veillée de prière')
        ->assertJsonPath('data.pinned', true);
});

test('authenticated user can view an announcement', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $announcement = Announcement::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/announcements/{$announcement->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $announcement->id);
});

test('authenticated user can update an announcement', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $announcement = Announcement::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/announcements/{$announcement->id}", [
            'title' => 'Annonce mise à jour',
            'pinned' => false,
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Annonce mise à jour')
        ->assertJsonPath('data.pinned', false);
});

test('authenticated user can delete an announcement', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $announcement = Announcement::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/announcements/{$announcement->id}")
        ->assertNoContent();

    expect(Announcement::find($announcement->id))->toBeNull();
});

test('announcements list is paginated', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Announcement::factory()->count(20)->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/announcements?per_page=5')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(5)
        ->and($response->json('meta.last_page'))->toBe(4);
});

test('announcements can be filtered by pinned', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Announcement::factory()->pinned()->count(2)->create(['tenant_id' => $tenant->id]);
    Announcement::factory()->count(3)->create(['tenant_id' => $tenant->id, 'pinned' => false]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/announcements?pinned=true')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

test('announcements can be filtered by active', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Announcement::factory()->active()->create(['tenant_id' => $tenant->id]);
    Announcement::factory()->expired()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/announcements?active=true')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

test('announcements can be filtered by target_group', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Announcement::factory()->create(['tenant_id' => $tenant->id, 'target_group' => 'youth']);
    Announcement::factory()->create(['tenant_id' => $tenant->id, 'target_group' => 'women']);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/announcements?target_group=youth')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

test('announcement expires_at must be after published_at', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/announcements', [
            'title' => 'Invalid Announcement',
            'slug' => 'invalid-announcement',
            'published_at' => '2026-03-15T12:00:00',
            'expires_at' => '2026-03-10T10:00:00',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('expires_at');
});
