<?php

use App\Models\Member;
use App\Models\MemberReadingProgress;
use App\Models\ReadingPlan;
use App\Models\ReadingPlanDay;
use App\Models\Tenant;

test('reading plan hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant->id]);

    expect($plan->toArray())->not->toHaveKey('tenant_id');
});

test('reading plan has many days', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant->id]);
    ReadingPlanDay::factory()->create([
        'tenant_id' => $tenant->id,
        'reading_plan_id' => $plan->id,
        'day_number' => 1,
    ]);
    ReadingPlanDay::factory()->create([
        'tenant_id' => $tenant->id,
        'reading_plan_id' => $plan->id,
        'day_number' => 2,
    ]);

    expect($plan->days)->toHaveCount(2);
});

test('reading plan days are ordered by day number', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant->id]);
    ReadingPlanDay::factory()->create([
        'tenant_id' => $tenant->id,
        'reading_plan_id' => $plan->id,
        'day_number' => 3,
    ]);
    ReadingPlanDay::factory()->create([
        'tenant_id' => $tenant->id,
        'reading_plan_id' => $plan->id,
        'day_number' => 1,
    ]);

    expect($plan->days->first()->day_number)->toBe(1)
        ->and($plan->days->last()->day_number)->toBe(3);
});

test('reading plan active scope works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    ReadingPlan::factory()->create(['tenant_id' => $tenant->id, 'is_active' => true]);
    ReadingPlan::factory()->inactive()->create(['tenant_id' => $tenant->id]);

    expect(ReadingPlan::active()->count())->toBe(1);
});

test('reading plan subscriber count accessor works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant->id]);
    $member1 = Member::factory()->create(['tenant_id' => $tenant->id]);
    $member2 = Member::factory()->create(['tenant_id' => $tenant->id]);

    MemberReadingProgress::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member1->id,
        'reading_plan_id' => $plan->id,
    ]);
    MemberReadingProgress::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member2->id,
        'reading_plan_id' => $plan->id,
    ]);

    expect($plan->subscriber_count)->toBe(2);
});

test('reading plan casts correctly', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $plan = ReadingPlan::factory()->create([
        'tenant_id' => $tenant->id,
        'is_active' => true,
        'duration_days' => 30,
    ]);

    $fresh = $plan->fresh();
    expect($fresh->is_active)->toBeTrue()
        ->and($fresh->duration_days)->toBeInt();
});

test('reading plan day belongs to reading plan', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant->id]);
    $day = ReadingPlanDay::factory()->create([
        'tenant_id' => $tenant->id,
        'reading_plan_id' => $plan->id,
        'day_number' => 1,
    ]);

    expect($day->readingPlan->id)->toBe($plan->id);
});

test('member reading progress belongs to member and plan', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant->id]);
    $progress = MemberReadingProgress::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'reading_plan_id' => $plan->id,
    ]);

    expect($progress->member->id)->toBe($member->id)
        ->and($progress->readingPlan->id)->toBe($plan->id);
});

test('member reading progress completed scope works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $plan = ReadingPlan::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);

    MemberReadingProgress::factory()->completed()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'reading_plan_id' => $plan->id,
    ]);
    MemberReadingProgress::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'reading_plan_id' => $plan->id,
        'reading_plan_day_id' => null,
    ]);

    expect(MemberReadingProgress::completed()->count())->toBe(1)
        ->and(MemberReadingProgress::notCompleted()->count())->toBe(1);
});

test('member reading progress streak factory state works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $progress = MemberReadingProgress::factory()->withStreak(5, 10)->create([
        'tenant_id' => $tenant->id,
    ]);

    expect($progress->current_streak)->toBe(5)
        ->and($progress->longest_streak)->toBe(10);
});
