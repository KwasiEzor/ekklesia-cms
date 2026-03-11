<?php

use App\Events\ContentChanged;
use App\Models\Attendance;
use App\Models\Member;
use App\Models\ServiceType;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake([ContentChanged::class]);
});

test('attendance belongs to tenant and cannot be accessed by another tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $member = Member::factory()->create(['tenant_id' => $tenant1->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant1->id]);
    $attendance = Attendance::factory()->create([
        'tenant_id' => $tenant1->id,
        'member_id' => $member->id,
        'service_type_id' => $serviceType->id,
    ]);

    tenancy()->initialize($tenant2);
    expect(Attendance::find($attendance->id))->toBeNull();
});

test('attendance count is isolated per tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $member1 = Member::factory()->create(['tenant_id' => $tenant1->id]);
    $serviceType1 = ServiceType::factory()->create(['tenant_id' => $tenant1->id]);
    Attendance::factory()->count(4)->create([
        'tenant_id' => $tenant1->id,
        'member_id' => $member1->id,
        'service_type_id' => $serviceType1->id,
    ]);

    tenancy()->initialize($tenant2);
    $member2 = Member::factory()->create(['tenant_id' => $tenant2->id]);
    $serviceType2 = ServiceType::factory()->create(['tenant_id' => $tenant2->id]);
    Attendance::factory()->count(2)->create([
        'tenant_id' => $tenant2->id,
        'member_id' => $member2->id,
        'service_type_id' => $serviceType2->id,
    ]);

    expect(Attendance::count())->toBe(2);

    tenancy()->initialize($tenant1);
    expect(Attendance::count())->toBe(4);
});

test('API returns only attendances for authenticated tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    $member1 = Member::factory()->create(['tenant_id' => $tenant1->id]);
    $serviceType1 = ServiceType::factory()->create(['tenant_id' => $tenant1->id]);
    Attendance::factory()->count(3)->create([
        'tenant_id' => $tenant1->id,
        'member_id' => $member1->id,
        'service_type_id' => $serviceType1->id,
    ]);

    tenancy()->initialize($tenant2);
    $member2 = Member::factory()->create(['tenant_id' => $tenant2->id]);
    $serviceType2 = ServiceType::factory()->create(['tenant_id' => $tenant2->id]);
    Attendance::factory()->count(7)->create([
        'tenant_id' => $tenant2->id,
        'member_id' => $member2->id,
        'service_type_id' => $serviceType2->id,
    ]);

    tenancy()->initialize($tenant1);

    $response = $this->actingAs($user1, 'sanctum')
        ->getJson('/api/v1/attendances')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(3);
});
