<?php

use App\Events\ContentChanged;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event as EventFacade;

beforeEach(function () {
    EventFacade::fake([ContentChanged::class]);
});

test('unauthenticated request to galleries returns 401', function () {
    $this->getJson('/api/v1/galleries')
        ->assertUnauthorized();
});

test('authenticated user can list galleries', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Gallery::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/galleries')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'slug', 'description', 'photo_count', 'photos', 'cover_url'],
            ],
            'links',
            'meta',
        ]);
});

test('galleries response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Gallery::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/galleries')
        ->assertOk();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('authenticated user can create a gallery', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/galleries', [
            'title' => 'Baptêmes de Noël',
            'description' => 'Photos de la cérémonie',
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Baptêmes de Noël')
        ->assertJsonPath('data.slug', 'baptemes-de-noel');
});

test('authenticated user can create a gallery linked to an event', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $event = Event::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/galleries', [
            'title' => 'Photos de l\'événement',
            'galleryable_type' => 'App\\Models\\Event',
            'galleryable_id' => $event->id,
        ])
        ->assertCreated()
        ->assertJsonPath('data.galleryable_type', 'Event')
        ->assertJsonPath('data.galleryable_id', $event->id);
});

test('authenticated user can view a gallery', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $gallery = Gallery::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/galleries/{$gallery->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $gallery->id);
});

test('authenticated user can update a gallery', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $gallery = Gallery::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/galleries/{$gallery->id}", [
            'title' => 'Titre modifié',
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Titre modifié');
});

test('authenticated user can delete a gallery', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $gallery = Gallery::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/galleries/{$gallery->id}")
        ->assertNoContent();

    expect(Gallery::find($gallery->id))->toBeNull();
});

test('galleries list is paginated', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Gallery::factory()->count(20)->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/galleries?per_page=5')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(5)
        ->and($response->json('meta.last_page'))->toBe(4);
});

test('galleries can be searched by title', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Gallery::factory()->create(['tenant_id' => $tenant->id, 'title' => 'Baptêmes de Pâques']);
    Gallery::factory()->create(['tenant_id' => $tenant->id, 'title' => 'Conférence annuelle']);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/galleries?search=Baptêmes')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.title'))->toBe('Baptêmes de Pâques');
});

test('galleries can be filtered by galleryable', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $event = Event::factory()->create(['tenant_id' => $tenant->id]);

    Gallery::factory()->count(2)->create([
        'tenant_id' => $tenant->id,
        'galleryable_type' => Event::class,
        'galleryable_id' => $event->id,
    ]);
    Gallery::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/galleries?galleryable_type=App%5CModels%5CEvent&galleryable_id={$event->id}")
        ->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});
