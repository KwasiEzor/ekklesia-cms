<?php

use App\Models\PaymentTransaction;
use App\Models\PlanLimit;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    PlanLimit::firstOrCreate(['plan_slug' => 'basic'], [
        'name' => 'Basic',
        'max_members' => 100,
        'max_storage_mb' => 500,
        'max_campuses' => 1,
        'has_payments' => true,
        'has_sms' => false,
        'has_whatsapp' => false,
        'has_ai' => false,
        'price_cents' => 1500,
        'currency' => 'USD',
    ]);
});

function createTenantWithPayments(): Tenant
{
    $tenant = Tenant::factory()->create();
    $tenant->plan_slug = 'basic';
    $tenant->save();

    return $tenant;
}

test('unauthenticated request to payments returns 401', function () {
    $this->getJson('/api/v1/payments')
        ->assertUnauthorized();
});

test('authenticated user can list payment transactions', function () {
    $tenant = createTenantWithPayments();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    PaymentTransaction::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/payments')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'uuid', 'amount', 'currency', 'provider', 'status', 'is_completed', 'is_pending'],
            ],
            'links',
            'meta',
        ]);
});

test('payments response does not expose tenant_id', function () {
    $tenant = createTenantWithPayments();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    PaymentTransaction::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/payments')
        ->assertOk();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('authenticated user can view a payment by uuid', function () {
    $tenant = createTenantWithPayments();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $transaction = PaymentTransaction::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/payments/{$transaction->uuid}")
        ->assertOk()
        ->assertJsonPath('data.uuid', $transaction->uuid);
});

test('payments list is paginated', function () {
    $tenant = createTenantWithPayments();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    PaymentTransaction::factory()->count(20)->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/payments?per_page=5')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(5)
        ->and($response->json('meta.last_page'))->toBe(4);
});

test('payments can be filtered by status', function () {
    $tenant = createTenantWithPayments();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    PaymentTransaction::factory()->completed()->count(2)->create(['tenant_id' => $tenant->id]);
    PaymentTransaction::factory()->failed()->create(['tenant_id' => $tenant->id]);
    PaymentTransaction::factory()->create(['tenant_id' => $tenant->id, 'status' => 'pending']);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/payments?status=completed')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

test('payments can be filtered by provider', function () {
    $tenant = createTenantWithPayments();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    PaymentTransaction::factory()->cinetpay()->count(3)->create(['tenant_id' => $tenant->id]);
    PaymentTransaction::factory()->stripe()->count(2)->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/payments?provider=cinetpay')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(3);
});
