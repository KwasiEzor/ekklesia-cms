<?php

use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\PlanLimit;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['auth:sanctum', InitializeTenancyByUser::class, 'plan:payments'])
        ->get('/api/v1/test-plan-middleware', fn () => response()->json(['ok' => true]));
});

test('middleware blocks feature when not available on plan', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    PlanLimit::create([
        'plan_slug' => 'free',
        'name' => 'Free',
        'has_payments' => false,
    ]);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/test-plan-middleware');

    expect($response->status())->toBe(403)
        ->and($response->json('upgrade_required'))->toBeTrue();
});

test('middleware allows feature when available on plan', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);
    $tenant->plan_slug = 'premium';
    $tenant->save();

    PlanLimit::create([
        'plan_slug' => 'premium',
        'name' => 'Premium',
        'has_payments' => true,
    ]);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/test-plan-middleware');

    expect($response->status())->toBe(200);
});
