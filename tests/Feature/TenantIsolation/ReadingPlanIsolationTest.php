<?php

use App\Models\ReadingPlan;
use App\Models\ReadingPlanDay;
use App\Models\Tenant;

test('reading plan belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant1->id]);

    tenancy()->initialize($tenant2);
    expect(ReadingPlan::find($plan->id))->toBeNull();
});

test('reading plan day belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant1->id]);
    $day = ReadingPlanDay::factory()->create([
        'tenant_id' => $tenant1->id,
        'reading_plan_id' => $plan->id,
        'day_number' => 1,
    ]);

    tenancy()->initialize($tenant2);
    expect(ReadingPlanDay::find($day->id))->toBeNull();
});

test('member reading progress is tenant isolated', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant1->id]);
    $progress = \App\Models\MemberReadingProgress::factory()->create([
        'tenant_id' => $tenant1->id,
        'reading_plan_id' => $plan->id,
    ]);

    tenancy()->initialize($tenant2);
    expect(\App\Models\MemberReadingProgress::find($progress->id))->toBeNull();
});
