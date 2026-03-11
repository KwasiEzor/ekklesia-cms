<?php

use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;

test('unauthenticated request to upcoming birthdays returns 401', function () {
    $this->getJson('/api/v1/members/upcoming/birthdays')
        ->assertUnauthorized();
});

test('authenticated user can list upcoming birthdays', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    Member::factory()->birthdayToday()->create(['tenant_id' => $tenant->id]);
    Member::factory()->create([
        'tenant_id' => $tenant->id,
        'date_of_birth' => now()->addDays(5)->subYears(25),
    ]);
    Member::factory()->create(['tenant_id' => $tenant->id, 'date_of_birth' => null]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/members/upcoming/birthdays')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(2);
});

test('upcoming birthdays can be filtered by days', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    Member::factory()->birthdayToday()->create(['tenant_id' => $tenant->id]);
    Member::factory()->create([
        'tenant_id' => $tenant->id,
        'date_of_birth' => now()->addDays(20)->subYears(30),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/members/upcoming/birthdays?days=7')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1);
});

test('upcoming birthdays response includes date_of_birth', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Member::factory()->birthdayToday()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/members/upcoming/birthdays')
        ->assertSuccessful();

    expect($response->json('data.0'))->toHaveKey('date_of_birth');
});

test('unauthenticated request to upcoming anniversaries returns 401', function () {
    $this->getJson('/api/v1/members/upcoming/anniversaries')
        ->assertUnauthorized();
});

test('authenticated user can list upcoming anniversaries', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    Member::factory()->anniversaryToday()->create(['tenant_id' => $tenant->id]);
    Member::factory()->create(['tenant_id' => $tenant->id, 'wedding_anniversary' => null]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/members/upcoming/anniversaries')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1);
});

test('member api response includes date_of_birth and wedding_anniversary', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    Member::factory()->create([
        'tenant_id' => $tenant->id,
        'date_of_birth' => '1990-05-15',
        'wedding_anniversary' => '2015-06-20',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/members')
        ->assertSuccessful();

    expect($response->json('data.0'))
        ->toHaveKey('date_of_birth', '1990-05-15')
        ->toHaveKey('wedding_anniversary', '2015-06-20');
});

test('member can be created with date_of_birth and wedding_anniversary', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/members', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'date_of_birth' => '1985-03-20',
            'wedding_anniversary' => '2010-07-15',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.date_of_birth', '1985-03-20')
        ->assertJsonPath('data.wedding_anniversary', '2010-07-15');
});
