<?php

use App\Models\Tenant;

test('security headers are present on responses', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = \App\Models\User::factory()->create(['tenant_id' => $tenant->id]);
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->getJson('/api/v1/sermons');

    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    $response->assertHeader('X-XSS-Protection', '0');
});

test('API rate limiting returns 429 after exceeding limit', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = \App\Models\User::factory()->create(['tenant_id' => $tenant->id]);
    $token = $user->createToken('test')->plainTextToken;

    // Make 61 requests (limit is 60/minute)
    for ($i = 0; $i < 61; $i++) {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/sermons');
    }

    $response->assertStatus(429);
});
