<?php

use App\Models\Attendance;
use App\Models\Member;
use App\Models\ServiceType;
use App\Models\Tenant;
use App\Models\User;

test('unauthenticated request to attendances returns 401', function () {
    $this->getJson('/api/v1/attendances')
        ->assertUnauthorized();
});

test('authenticated user can list attendances', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant->id]);

    Attendance::factory()->count(3)->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'service_type_id' => $serviceType->id,
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/attendances')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'member_id', 'date', 'check_in_method', 'is_first_time'],
            ],
            'links',
            'meta',
        ]);
});

test('attendances response does not expose tenant_id', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant->id]);

    Attendance::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'service_type_id' => $serviceType->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/attendances')
        ->assertOk();

    expect($response->json('data.0'))->not->toHaveKey('tenant_id');
});

test('authenticated user can record attendance', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/attendances', [
            'member_id' => $member->id,
            'service_type_id' => $serviceType->id,
            'date' => '2026-03-09',
            'checked_in_at' => '09:15:00',
            'check_in_method' => 'manual',
            'is_first_time' => false,
        ])
        ->assertCreated()
        ->assertJsonPath('data.member_id', $member->id)
        ->assertJsonPath('data.date', '2026-03-09');
});

test('authenticated user can record first-time visitor', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/attendances', [
            'member_id' => $member->id,
            'service_type_id' => $serviceType->id,
            'date' => '2026-03-09',
            'is_first_time' => true,
        ])
        ->assertCreated()
        ->assertJsonPath('data.is_first_time', true);
});

test('authenticated user can update attendance', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant->id]);

    $attendance = Attendance::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'service_type_id' => $serviceType->id,
    ]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/attendances/{$attendance->id}", [
            'checked_out_at' => '12:00:00',
            'notes' => 'Parti tôt',
        ])
        ->assertOk()
        ->assertJsonPath('data.notes', 'Parti tôt');
});

test('authenticated user can delete attendance', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant->id]);

    $attendance = Attendance::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'service_type_id' => $serviceType->id,
    ]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/attendances/{$attendance->id}")
        ->assertNoContent();

    expect(Attendance::find($attendance->id))->toBeNull();
});

test('attendances can be filtered by date', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant->id]);

    Attendance::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'service_type_id' => $serviceType->id,
        'date' => '2026-03-09',
    ]);
    Attendance::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member->id,
        'service_type_id' => $serviceType->id,
        'date' => '2026-03-02',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/attendances?date=2026-03-09')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

test('attendances can be filtered by first timers', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member1 = Member::factory()->create(['tenant_id' => $tenant->id]);
    $member2 = Member::factory()->create(['tenant_id' => $tenant->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant->id]);

    Attendance::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member1->id,
        'service_type_id' => $serviceType->id,
        'is_first_time' => true,
    ]);
    Attendance::factory()->create([
        'tenant_id' => $tenant->id,
        'member_id' => $member2->id,
        'service_type_id' => $serviceType->id,
        'is_first_time' => false,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/attendances?is_first_time=true')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

test('attendances list is paginated', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->create(['tenant_id' => $tenant->id]);
    $serviceType = ServiceType::factory()->create(['tenant_id' => $tenant->id]);

    for ($i = 0; $i < 20; $i++) {
        Attendance::factory()->create([
            'tenant_id' => $tenant->id,
            'member_id' => $member->id,
            'service_type_id' => $serviceType->id,
            'date' => now()->subDays($i),
        ]);
    }

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/attendances?per_page=5')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(5)
        ->and($response->json('meta.last_page'))->toBe(4);
});
