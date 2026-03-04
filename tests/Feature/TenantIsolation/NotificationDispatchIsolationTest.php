<?php

use App\Models\NotificationDispatch;
use App\Models\Tenant;

test('notification dispatch belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $dispatch = NotificationDispatch::factory()->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    expect(NotificationDispatch::find($dispatch->id))->toBeNull();
});

test('notification dispatch count is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    NotificationDispatch::factory()->count(5)->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    NotificationDispatch::factory()->count(2)->create(['tenant_id' => $tenant2->id]);

    expect(NotificationDispatch::count())->toBe(2);

    tenancy()->initialize($tenant1);
    expect(NotificationDispatch::count())->toBe(5);
});
