<?php

use App\Models\Campaign;
use App\Models\Fund;
use App\Models\GivingRecord;
use App\Models\Member;
use App\Models\Tenant;

test('campaign hides tenant_id from serialization', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $campaign = Campaign::factory()->create(['tenant_id' => $tenant->id]);

    expect($campaign->toArray())->not->toHaveKey('tenant_id');
});

test('campaign belongs to fund', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $fund = Fund::factory()->create(['tenant_id' => $tenant->id]);
    $campaign = Campaign::factory()->create([
        'tenant_id' => $tenant->id,
        'fund_id' => $fund->id,
    ]);

    expect($campaign->fund->id)->toBe($fund->id);
});

test('campaign has many giving records', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $campaign = Campaign::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);

    GivingRecord::factory()->count(4)->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'campaign_id' => $campaign->id,
    ]);

    expect($campaign->givingRecords)->toHaveCount(4);
});

test('campaign scope active works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Campaign::factory()->create(['tenant_id' => $tenant->id, 'status' => 'active']);
    Campaign::factory()->completed()->create(['tenant_id' => $tenant->id]);
    Campaign::factory()->draft()->create(['tenant_id' => $tenant->id]);

    expect(Campaign::active()->count())->toBe(1);
});

test('campaign scope completed works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Campaign::factory()->create(['tenant_id' => $tenant->id, 'status' => 'active']);
    Campaign::factory()->completed()->create(['tenant_id' => $tenant->id]);

    expect(Campaign::completed()->count())->toBe(1);
});

test('campaign raised_amount accessor works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $campaign = Campaign::factory()->create([
        'tenant_id' => $tenant->id,
        'goal_amount' => 1000000,
    ]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);

    GivingRecord::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'campaign_id' => $campaign->id,
        'amount' => 250000,
    ]);
    GivingRecord::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'campaign_id' => $campaign->id,
        'amount' => 150000,
    ]);

    expect($campaign->raised_amount)->toBe(400000.0);
});

test('campaign progress_percent accessor works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $campaign = Campaign::factory()->create([
        'tenant_id' => $tenant->id,
        'goal_amount' => 1000000,
    ]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);

    GivingRecord::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'campaign_id' => $campaign->id,
        'amount' => 500000,
    ]);

    expect($campaign->progress_percent)->toBe(50.0);
});

test('campaign progress_percent returns null when no goal', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $campaign = Campaign::factory()->withoutGoal()->create(['tenant_id' => $tenant->id]);

    expect($campaign->progress_percent)->toBeNull();
});

test('campaign donor_count accessor works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $campaign = Campaign::factory()->create(['tenant_id' => $tenant->id]);
    $member1 = Member::factory()->create(['tenant_id' => $tenant->id]);
    $member2 = Member::factory()->create(['tenant_id' => $tenant->id]);

    GivingRecord::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member1->id,
        'campaign_id' => $campaign->id,
    ]);
    GivingRecord::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member2->id,
        'campaign_id' => $campaign->id,
    ]);
    // Same member donates again
    GivingRecord::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member1->id,
        'campaign_id' => $campaign->id,
    ]);

    expect($campaign->donor_count)->toBe(2);
});

test('campaign is_overdue accessor works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $overdue = Campaign::factory()->create([
        'tenant_id' => $tenant->id,
        'status' => 'active',
        'end_date' => now()->subDay(),
    ]);

    $notOverdue = Campaign::factory()->create([
        'tenant_id' => $tenant->id,
        'status' => 'active',
        'end_date' => now()->addMonth(),
    ]);

    expect($overdue->is_overdue)->toBeTrue()
        ->and($notOverdue->is_overdue)->toBeFalse();
});

test('campaign markAsCompleted works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $campaign = Campaign::factory()->create([
        'tenant_id' => $tenant->id,
        'status' => 'active',
    ]);

    $campaign->markAsCompleted();

    expect($campaign->fresh()->status)->toBe('completed');
});

test('campaign casts dates correctly', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $campaign = Campaign::factory()->create([
        'tenant_id' => $tenant->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
    ]);

    $fresh = $campaign->fresh();
    expect($fresh->start_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($fresh->end_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});
