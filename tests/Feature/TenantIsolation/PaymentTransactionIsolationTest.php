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

test('payment transaction belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $transaction = PaymentTransaction::factory()->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    expect(PaymentTransaction::find($transaction->id))->toBeNull();
});

test('payment transaction count is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    PaymentTransaction::factory()->count(4)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    PaymentTransaction::factory()->count(2)->create(['tenant_id' => $tenant2->id]);

    expect(PaymentTransaction::count())->toBe(2);

    tenancy()->initialize($tenant1);
    expect(PaymentTransaction::count())->toBe(4);
});

test('API returns only payment transactions for authenticated tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant1->plan_slug = 'basic';
    $tenant1->save();

    $tenant2 = Tenant::factory()->create();
    $tenant2->plan_slug = 'basic';
    $tenant2->save();

    tenancy()->initialize($tenant1);
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    PaymentTransaction::factory()->count(3)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);
    PaymentTransaction::factory()->count(7)->create(['tenant_id' => $tenant2->id]);

    tenancy()->initialize($tenant1);

    $response = $this->actingAs($user1, 'sanctum')
        ->getJson('/api/v1/payments')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(3);
});
