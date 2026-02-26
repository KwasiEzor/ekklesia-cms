<?php

use App\Models\Page;
use App\Models\Tenant;
use App\Models\User;

test('unauthenticated request to pages returns 401', function () {
    $this->getJson('/api/v1/pages')
        ->assertUnauthorized();
});

test('authenticated user can list pages', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Page::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/pages')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'slug', 'content_blocks', 'seo_title', 'seo_description', 'published_at', 'is_published'],
            ],
            'links',
            'meta',
        ]);
});

test('pages response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Page::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/pages')
        ->assertOk();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('authenticated user can create a page', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/pages', [
            'title' => 'À propos de notre église',
            'slug' => 'a-propos',
            'content_blocks' => [
                ['type' => 'heading', 'data' => ['level' => 'h2', 'content' => 'Notre mission']],
                ['type' => 'rich_text', 'data' => ['body' => 'Nous sommes une église dynamique.']],
            ],
            'seo_title' => 'À propos - Notre Église',
            'seo_description' => 'Découvrez notre mission et notre vision.',
            'published_at' => '2026-03-01T08:00:00',
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'À propos de notre église')
        ->assertJsonPath('data.slug', 'a-propos')
        ->assertJsonPath('data.seo_title', 'À propos - Notre Église');
});

test('authenticated user can view a page', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $page = Page::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/pages/{$page->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $page->id);
});

test('authenticated user can update a page with content_blocks', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $page = Page::factory()->create(['tenant_id' => $tenant->id]);

    $blocks = [
        ['type' => 'heading', 'data' => ['level' => 'h2', 'content' => 'Mis à jour']],
        ['type' => 'quote', 'data' => ['text' => 'Jean 3:16', 'attribution' => 'La Bible']],
    ];

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/pages/{$page->id}", [
            'title' => 'Page mise à jour',
            'content_blocks' => $blocks,
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Page mise à jour')
        ->assertJsonPath('data.content_blocks.0.type', 'heading')
        ->assertJsonPath('data.content_blocks.1.type', 'quote');
});

test('authenticated user can delete a page', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $page = Page::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/pages/{$page->id}")
        ->assertNoContent();

    expect(Page::find($page->id))->toBeNull();
});

test('pages list is paginated', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Page::factory()->count(20)->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/pages?per_page=5')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(5)
        ->and($response->json('meta.last_page'))->toBe(4);
});

test('pages can be filtered by published', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Page::factory()->published()->count(2)->create(['tenant_id' => $tenant->id]);
    Page::factory()->draft()->count(3)->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/pages?published=true')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

test('pages can be searched by title', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Page::factory()->create(['tenant_id' => $tenant->id, 'title' => 'Accueil de notre église']);
    Page::factory()->create(['tenant_id' => $tenant->id, 'title' => 'Contact']);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/pages?search=Accueil')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.title'))->toBe('Accueil de notre église');
});
