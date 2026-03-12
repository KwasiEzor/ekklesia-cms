<?php

use App\Models\PlanLimit;
use App\Models\Tenant;
use App\Models\User;
use Laravel\Cashier\Cashier;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->tenant = Tenant::create([
        'id' => 'api-tenant',
        'name' => 'API Tenant',
        'slug' => 'api-tenant',
        'plan_slug' => 'free',
    ]);

    tenancy()->initialize($this->tenant);

    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);

    actingAs($this->user);

    PlanLimit::create([
        'plan_slug' => 'free',
        'name' => 'Free',
        'price_cents' => 0,
    ]);

    PlanLimit::create([
        'plan_slug' => 'pro',
        'name' => 'Pro',
        'price_cents' => 2900,
        'stripe_price_id' => 'price_pro_123',
    ]);
});

test('can get subscription status', function () {
    getJson('/api/v1/subscriptions/status')
        ->assertStatus(200)
        ->assertJsonPath('tenant.plan_slug', 'free')
        ->assertJsonPath('subscription', null);
});

test('can list plans', function () {
    getJson('/api/v1/subscriptions/plans')
        ->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.plan_slug', 'free')
        ->assertJsonPath('data.1.plan_slug', 'pro');
});

test('subscribe requires a valid plan', function () {
    postJson('/api/v1/subscriptions/subscribe', ['plan_slug' => 'invalid'])
        ->assertStatus(422);
});

test('subscribe with non-purchasable plan fails', function () {
    postJson('/api/v1/subscriptions/subscribe', ['plan_slug' => 'free'])
        ->assertStatus(422)
        ->assertJson(['message' => 'This plan is not available for online purchase.']);
});

test('portal fails if no stripe id', function () {
    postJson('/api/v1/subscriptions/portal')
        ->assertStatus(404);
});
