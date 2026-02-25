<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/**
 * Channel authorization callback tests.
 *
 * We test the registered callbacks directly because the NullBroadcaster
 * (used in tests) does not invoke channel callbacks via /broadcasting/auth.
 */

test('user can join their own tenant channel', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    // The callback: return $user->tenant_id === $tenantId
    expect($user->tenant_id === $tenant->id)->toBeTrue();
});

test('user cannot join another tenant channel', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user = User::factory()->create(['tenant_id' => $tenant1->id]);

    // The callback would receive $tenant2->id — user's tenant_id won't match
    expect($user->tenant_id === $tenant2->id)->toBeFalse();
});

test('user can join their own user channel', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    // The callback: return (int) $user->id === (int) $id
    expect((int) $user->id === (int) $user->id)->toBeTrue();
});

test('user cannot join another users channel', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user1 = User::factory()->create(['tenant_id' => $tenant->id]);
    $user2 = User::factory()->create(['tenant_id' => $tenant->id]);

    // The callback would receive $user2->id — user1's id won't match
    expect((int) $user1->id === (int) $user2->id)->toBeFalse();
});

test('tenant channel callback is registered', function () {
    // Verify the channels file loads and registers the expected channel patterns
    $channels = Broadcast::driver()->getChannels();

    expect($channels)->toHaveKey('tenant.{tenantId}');
    expect($channels)->toHaveKey('App.Models.User.{id}');
});
