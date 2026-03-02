<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    Event::fake([\App\Events\ContentChanged::class]);
    $this->tenant = Tenant::factory()->create();
    tenancy()->initialize($this->tenant);
});

// --- Registration ---

test('user can register with valid data', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Jean Dupont',
        'email' => 'jean@example.com',
        'password' => 'SecureP@ss123',
        'password_confirmation' => 'SecureP@ss123',
        'device_name' => 'iPhone de Jean',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

    expect($response->json('user.name'))->toBe('Jean Dupont');
    expect($response->json('user.email'))->toBe('jean@example.com');
    expect($response->json('token'))->toBeString();

    $this->assertDatabaseHas('users', [
        'email' => 'jean@example.com',
        'tenant_id' => $this->tenant->id,
    ]);
});

test('registration fails with duplicate email in same tenant', function () {
    User::factory()->create([
        'email' => 'jean@example.com',
        'tenant_id' => $this->tenant->id,
    ]);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Another Jean',
        'email' => 'jean@example.com',
        'password' => 'SecureP@ss123',
        'password_confirmation' => 'SecureP@ss123',
        'device_name' => 'Android',
    ])->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

test('registration allows same email in different tenant', function () {
    User::factory()->create([
        'email' => 'jean@example.com',
        'tenant_id' => $this->tenant->id,
    ]);

    $tenant2 = Tenant::factory()->create();
    tenancy()->initialize($tenant2);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Jean Dupont',
        'email' => 'jean@example.com',
        'password' => 'SecureP@ss123',
        'password_confirmation' => 'SecureP@ss123',
        'device_name' => 'Android',
    ])->assertStatus(201);
});

test('registration requires password confirmation', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Jean Dupont',
        'email' => 'jean@example.com',
        'password' => 'SecureP@ss123',
        'device_name' => 'iPhone',
    ])->assertStatus(422)
        ->assertJsonValidationErrors('password');
});

// --- Login ---

test('user can login with valid credentials', function () {
    User::factory()->create([
        'email' => 'jean@example.com',
        'password' => Hash::make('SecureP@ss123'),
        'tenant_id' => $this->tenant->id,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'jean@example.com',
        'password' => 'SecureP@ss123',
        'device_name' => 'iPhone de Jean',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);
});

test('login fails with wrong password', function () {
    User::factory()->create([
        'email' => 'jean@example.com',
        'password' => Hash::make('SecureP@ss123'),
        'tenant_id' => $this->tenant->id,
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'jean@example.com',
        'password' => 'WrongPassword',
        'device_name' => 'iPhone',
    ])->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

test('login fails with nonexistent email', function () {
    $this->postJson('/api/v1/auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'SomePassword123',
        'device_name' => 'iPhone',
    ])->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

// --- Logout ---

test('authenticated user can logout', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/auth/logout')
        ->assertStatus(204);
});

test('unauthenticated user cannot logout', function () {
    $this->postJson('/api/v1/auth/logout')
        ->assertUnauthorized();
});

// --- Me ---

test('authenticated user can get their profile', function () {
    $user = User::factory()->create([
        'name' => 'Jean Dupont',
        'email' => 'jean@example.com',
        'tenant_id' => $this->tenant->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/me');

    $response->assertOk()
        ->assertJson([
            'id' => $user->id,
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
        ]);
});

test('me endpoint does not expose tenant_id or password', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/me');

    $response->assertOk();
    expect($response->json())->not->toHaveKey('tenant_id');
    expect($response->json())->not->toHaveKey('password');
});

// --- Token Management ---

test('user can list their tokens', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $user->createToken('iPhone');
    $user->createToken('Android');

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/tokens');

    $response->assertOk()
        ->assertJsonCount(2, 'data'); // actingAs doesn't create a DB token
});

test('user can revoke a specific token', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $token = $user->createToken('to-delete');
    $tokenId = $user->tokens()->where('name', 'to-delete')->first()->id;

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/auth/tokens/{$tokenId}")
        ->assertStatus(204);

    expect($user->tokens()->where('name', 'to-delete')->exists())->toBeFalse();
});

test('user cannot revoke another users token', function () {
    $user1 = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $user2 = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $user2->createToken('other-token');
    $tokenId = $user2->tokens()->first()->id;

    $this->actingAs($user1, 'sanctum')
        ->deleteJson("/api/v1/auth/tokens/{$tokenId}")
        ->assertStatus(404);
});

test('user can revoke all their tokens', function () {
    $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $user->createToken('iPhone');
    $user->createToken('Android');
    $user->createToken('iPad');

    $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/v1/auth/tokens')
        ->assertStatus(204);

    expect($user->tokens()->count())->toBe(0);
});

// --- Auth rate limiting ---

test('auth endpoints are rate limited', function () {
    for ($i = 0; $i < 6; $i++) {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong',
            'device_name' => 'test',
        ]);
    }

    $response->assertStatus(429);
});
