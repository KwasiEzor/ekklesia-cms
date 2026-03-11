<?php

use App\Models\Member;
use App\Models\Tenant;

test('member birthday today scope works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Member::factory()->birthdayToday()->create(['tenant_id' => $tenant->id]);
    Member::factory()->create(['tenant_id' => $tenant->id, 'date_of_birth' => now()->subMonths(3)]);
    Member::factory()->create(['tenant_id' => $tenant->id, 'date_of_birth' => null]);

    expect(Member::birthdayToday()->count())->toBe(1);
});

test('member birthday this week scope works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Member::factory()->birthdayToday()->create(['tenant_id' => $tenant->id]);
    Member::factory()->create([
        'tenant_id' => $tenant->id,
        'date_of_birth' => now()->addDays(3)->subYears(25),
    ]);
    Member::factory()->create([
        'tenant_id' => $tenant->id,
        'date_of_birth' => now()->addDays(10)->subYears(30),
    ]);
    Member::factory()->create(['tenant_id' => $tenant->id, 'date_of_birth' => null]);

    expect(Member::birthdayThisWeek()->count())->toBe(2);
});

test('member upcoming birthdays scope works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Member::factory()->birthdayToday()->create(['tenant_id' => $tenant->id]);
    Member::factory()->create([
        'tenant_id' => $tenant->id,
        'date_of_birth' => now()->addDays(15)->subYears(20),
    ]);
    Member::factory()->create(['tenant_id' => $tenant->id, 'date_of_birth' => null]);

    expect(Member::upcomingBirthdays(30)->count())->toBe(2);
});

test('member anniversary today scope works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Member::factory()->anniversaryToday()->create(['tenant_id' => $tenant->id]);
    Member::factory()->create(['tenant_id' => $tenant->id, 'wedding_anniversary' => now()->subMonths(2)]);
    Member::factory()->create(['tenant_id' => $tenant->id, 'wedding_anniversary' => null]);

    expect(Member::anniversaryToday()->count())->toBe(1);
});

test('member anniversary this week scope works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    Member::factory()->anniversaryToday()->create(['tenant_id' => $tenant->id]);
    Member::factory()->create([
        'tenant_id' => $tenant->id,
        'wedding_anniversary' => now()->addDays(5)->subYears(10),
    ]);
    Member::factory()->create(['tenant_id' => $tenant->id, 'wedding_anniversary' => null]);

    expect(Member::anniversaryThisWeek()->count())->toBe(2);
});

test('member age accessor works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'date_of_birth' => now()->subYears(30),
    ]);

    expect($member->age)->toBe(30);
});

test('member age accessor returns null when no date of birth', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'date_of_birth' => null,
    ]);

    expect($member->age)->toBeNull();
});

test('member years married accessor works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'wedding_anniversary' => now()->subYears(15),
    ]);

    expect($member->years_married)->toBe(15);
});

test('member isBirthdayToday method works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $birthdayMember = Member::factory()->birthdayToday()->create(['tenant_id' => $tenant->id]);
    $otherMember = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'date_of_birth' => now()->subMonths(3)->subYears(20),
    ]);

    expect($birthdayMember->isBirthdayToday())->toBeTrue()
        ->and($otherMember->isBirthdayToday())->toBeFalse();
});

test('member isAnniversaryToday method works', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $anniversaryMember = Member::factory()->anniversaryToday()->create(['tenant_id' => $tenant->id]);
    $otherMember = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'wedding_anniversary' => now()->subMonths(3)->subYears(5),
    ]);

    expect($anniversaryMember->isAnniversaryToday())->toBeTrue()
        ->and($otherMember->isAnniversaryToday())->toBeFalse();
});

test('member date_of_birth and wedding_anniversary are cast to dates', function () {
    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $member = Member::factory()->create([
        'tenant_id' => $tenant->id,
        'date_of_birth' => '1990-05-15',
        'wedding_anniversary' => '2015-06-20',
    ]);

    $fresh = $member->fresh();
    expect($fresh->date_of_birth)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($fresh->wedding_anniversary)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});
