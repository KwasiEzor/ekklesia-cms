<?php

use App\Models\Member;
use App\Models\MemberReadingProgress;
use App\Models\ReadingPlan;
use App\Models\ReadingPlanDay;
use App\Models\Tenant;
use App\Models\User;

test('unauthenticated request to reading plans returns 401', function () {
    $this->getJson('/api/v1/reading-plans')
        ->assertUnauthorized();
});

test('authenticated user can list reading plans', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    ReadingPlan::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/reading-plans')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'slug', 'duration_days', 'is_active'],
            ],
            'links',
            'meta',
        ]);
});

test('reading plans response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    ReadingPlan::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/reading-plans')
        ->assertSuccessful();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('reading plans can be filtered by active status', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    ReadingPlan::factory()->create(['tenant_id' => $tenant->id, 'is_active' => true]);
    ReadingPlan::factory()->inactive()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/reading-plans?active=true')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1);
});

test('authenticated user can create a reading plan', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/reading-plans', [
            'title' => 'Les Psaumes en 30 jours',
            'description' => 'Un parcours de lecture des Psaumes.',
            'duration_days' => 30,
            'grace_period_days' => 1,
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Les Psaumes en 30 jours')
        ->assertJsonPath('data.duration_days', 30);
});

test('authenticated user can create a reading plan with days', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/reading-plans', [
            'title' => 'Jean en 3 jours',
            'duration_days' => 3,
            'days' => [
                ['day_number' => 1, 'passage_reference' => 'Jean 1-7'],
                ['day_number' => 2, 'passage_reference' => 'Jean 8-14'],
                ['day_number' => 3, 'passage_reference' => 'Jean 15-21'],
            ],
        ])
        ->assertCreated();

    expect($response->json('data.days'))->toHaveCount(3);
});

test('authenticated user can view a reading plan with days', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant->id]);
    ReadingPlanDay::factory()->create([
        'tenant_id' => $tenant->id,
        'reading_plan_id' => $plan->id,
        'day_number' => 1,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/reading-plans/{$plan->id}")
        ->assertSuccessful();

    expect($response->json('data.days'))->toHaveCount(1);
});

test('authenticated user can update a reading plan', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/reading-plans/{$plan->id}", [
            'title' => 'Plan mis à jour',
            'is_active' => false,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'Plan mis à jour')
        ->assertJsonPath('data.is_active', false);
});

test('authenticated user can delete a reading plan', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/reading-plans/{$plan->id}")
        ->assertNoContent();

    expect(ReadingPlan::find($plan->id))->toBeNull();
});

test('member can subscribe to a reading plan', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/reading-plans/{$plan->id}/subscribe", [
            'member_id' => $member->id,
        ])
        ->assertCreated();

    expect(MemberReadingProgress::where('member_id', $member->id)->where('reading_plan_id', $plan->id)->exists())->toBeTrue();
});

test('duplicate subscription returns 409', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant->id]);

    MemberReadingProgress::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'reading_plan_id' => $plan->id,
        'reading_plan_day_id' => null,
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/reading-plans/{$plan->id}/subscribe", [
            'member_id' => $member->id,
        ])
        ->assertStatus(409);
});

test('member can complete a reading day', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant->id]);
    $day = ReadingPlanDay::factory()->create([
        'tenant_id' => $tenant->id,
        'reading_plan_id' => $plan->id,
        'day_number' => 1,
    ]);

    // Subscribe first
    MemberReadingProgress::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'reading_plan_id' => $plan->id,
        'reading_plan_day_id' => null,
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/reading-plans/{$plan->id}/days/{$day->id}/complete", [
            'member_id' => $member->id,
        ])
        ->assertSuccessful()
        ->assertJsonPath('current_streak', 1);
});

test('completing same day twice returns 409', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant->id]);
    $day = ReadingPlanDay::factory()->create([
        'tenant_id' => $tenant->id,
        'reading_plan_id' => $plan->id,
        'day_number' => 1,
    ]);

    MemberReadingProgress::factory()->completed()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'reading_plan_id' => $plan->id,
        'reading_plan_day_id' => $day->id,
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/reading-plans/{$plan->id}/days/{$day->id}/complete", [
            'member_id' => $member->id,
        ])
        ->assertStatus(409);
});

test('member can get progress for a reading plan', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant->id]);

    $day1 = ReadingPlanDay::factory()->create([
        'tenant_id' => $tenant->id, 'reading_plan_id' => $plan->id, 'day_number' => 1,
    ]);
    ReadingPlanDay::factory()->create([
        'tenant_id' => $tenant->id, 'reading_plan_id' => $plan->id, 'day_number' => 2,
    ]);

    MemberReadingProgress::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'reading_plan_id' => $plan->id,
        'reading_plan_day_id' => null,
        'current_streak' => 1,
        'longest_streak' => 1,
    ]);
    MemberReadingProgress::factory()->completed()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'reading_plan_id' => $plan->id,
        'reading_plan_day_id' => $day1->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/reading-plans/{$plan->id}/progress?member_id={$member->id}")
        ->assertSuccessful();

    expect($response->json('subscribed'))->toBeTrue()
        ->and($response->json('completed_days'))->toBe(1)
        ->and($response->json('total_days'))->toBe(2)
        ->and((float) $response->json('progress_percent'))->toBe(50.0);
});
