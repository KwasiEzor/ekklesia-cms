<?php

use App\Models\Tenant;
use App\Models\User;

test('settings route does not crash with internal server error for tenant context', function () {
    $tenant = Tenant::factory()->create([
        'slug' => 'rehoboth',
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $response = $this->actingAs($user)
        ->get("/admin/{$tenant->slug}/settings");

    // Access policy may still forbid this user (403), but route must not 500.
    expect($response->getStatusCode())->not->toBe(500);
});
