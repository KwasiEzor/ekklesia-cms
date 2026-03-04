<?php

use App\Models\Member;
use App\Models\PaymentTransaction;
use App\Models\Tenant;

test('payment transaction generates uuid on creation', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $transaction = PaymentTransaction::factory()->create(['tenant_id' => $tenant->id]);

    expect($transaction->uuid)->not->toBeNull()
        ->and($transaction->uuid)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
});

test('payment transaction casts amount as decimal', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $transaction = PaymentTransaction::factory()->create([
        'tenant_id' => $tenant->id,
        'amount' => 5000.50,
    ]);

    expect((float) $transaction->fresh()->amount)->toBe(5000.50);
});

test('payment transaction casts provider_metadata as array', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $transaction = PaymentTransaction::factory()->create([
        'tenant_id' => $tenant->id,
        'provider_metadata' => ['key' => 'value'],
    ]);

    expect($transaction->fresh()->provider_metadata)->toBe(['key' => 'value']);
});

test('payment transaction hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $transaction = PaymentTransaction::factory()->create(['tenant_id' => $tenant->id]);
    $array = $transaction->toArray();

    expect($array)->not->toHaveKey('tenant_id');
});

test('payment transaction belongs to member', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $transaction = PaymentTransaction::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
    ]);

    expect($transaction->member->id)->toBe($member->id);
});

test('payment transaction is_completed returns true for completed status', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $transaction = PaymentTransaction::factory()->completed()->create([
        'tenant_id' => $tenant->id,
    ]);

    expect($transaction->is_completed)->toBeTrue()
        ->and($transaction->is_pending)->toBeFalse();
});

test('payment transaction is_pending returns true for pending status', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $transaction = PaymentTransaction::factory()->create([
        'tenant_id' => $tenant->id,
        'status' => 'pending',
    ]);

    expect($transaction->is_pending)->toBeTrue()
        ->and($transaction->is_completed)->toBeFalse();
});

test('payment transaction formatted_amount includes currency', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $transaction = PaymentTransaction::factory()->create([
        'tenant_id' => $tenant->id,
        'amount' => 10000,
        'currency' => 'XOF',
    ]);

    expect($transaction->formatted_amount)->toContain('XOF');
});

test('mark as completed sets status and paid_at', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $transaction = PaymentTransaction::factory()->create([
        'tenant_id' => $tenant->id,
        'status' => 'pending',
    ]);

    $transaction->markAsCompleted('ref-123');
    $transaction->refresh();

    expect($transaction->status)->toBe('completed')
        ->and($transaction->paid_at)->not->toBeNull()
        ->and($transaction->provider_reference)->toBe('ref-123');
});

test('mark as failed sets status and failure reason', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $transaction = PaymentTransaction::factory()->create([
        'tenant_id' => $tenant->id,
        'status' => 'pending',
    ]);

    $transaction->markAsFailed('Insufficient funds');
    $transaction->refresh();

    expect($transaction->status)->toBe('failed')
        ->and($transaction->failed_at)->not->toBeNull()
        ->and($transaction->failure_reason)->toBe('Insufficient funds');
});
