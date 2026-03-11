<?php

use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\AnniversaryNotification;
use App\Notifications\BirthdayNotification;
use Illuminate\Support\Facades\Notification;

test('birthday notifications command sends birthday notifications', function () {
    Notification::fake();

    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    Member::factory()->birthdayToday()->create(['tenant_id' => $tenant->id]);

    tenancy()->end();

    $this->artisan('members:send-birthday-notifications')
        ->assertSuccessful();

    Notification::assertSentTo($admin, BirthdayNotification::class);
});

test('birthday notifications command sends anniversary notifications', function () {
    Notification::fake();

    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    Member::factory()->anniversaryToday()->create(['tenant_id' => $tenant->id]);

    tenancy()->end();

    $this->artisan('members:send-birthday-notifications')
        ->assertSuccessful();

    Notification::assertSentTo($admin, AnniversaryNotification::class);
});

test('birthday notifications command skips tenants with no birthdays or anniversaries', function () {
    Notification::fake();

    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    Member::factory()->create([
        'tenant_id' => $tenant->id,
        'date_of_birth' => now()->subMonths(3)->subYears(25),
        'wedding_anniversary' => null,
    ]);

    tenancy()->end();

    $this->artisan('members:send-birthday-notifications')
        ->assertSuccessful();

    Notification::assertNotSentTo($admin, BirthdayNotification::class);
    Notification::assertNotSentTo($admin, AnniversaryNotification::class);
});

test('birthday notifications command processes multiple tenants', function () {
    Notification::fake();

    $tenant1 = Tenant::factory()->create();
    tenancy()->initialize($tenant1);
    $admin1 = User::factory()->create(['tenant_id' => $tenant1->id]);
    Member::factory()->birthdayToday()->create(['tenant_id' => $tenant1->id]);
    tenancy()->end();

    $tenant2 = Tenant::factory()->create();
    tenancy()->initialize($tenant2);
    $admin2 = User::factory()->create(['tenant_id' => $tenant2->id]);
    Member::factory()->birthdayToday()->create(['tenant_id' => $tenant2->id]);
    tenancy()->end();

    $this->artisan('members:send-birthday-notifications')
        ->assertSuccessful();

    Notification::assertSentTo($admin1, BirthdayNotification::class);
    Notification::assertSentTo($admin2, BirthdayNotification::class);
});

test('birthday notification contains correct data', function () {
    Notification::fake();

    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->birthdayToday()->create([
        'tenant_id' => $tenant->id,
        'first_name' => 'Marie',
        'last_name' => 'Kouassi',
    ]);

    tenancy()->end();

    $this->artisan('members:send-birthday-notifications')
        ->assertSuccessful();

    Notification::assertSentTo($admin, BirthdayNotification::class, function ($notification) use ($member) {
        return $notification->member->id === $member->id;
    });
});

test('anniversary notification contains correct data', function () {
    Notification::fake();

    $tenant = Tenant::factory()->create();
    tenancy()->initialize($tenant);

    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    $member = Member::factory()->anniversaryToday()->create([
        'tenant_id' => $tenant->id,
        'first_name' => 'Paul',
        'last_name' => 'Mensah',
    ]);

    tenancy()->end();

    $this->artisan('members:send-birthday-notifications')
        ->assertSuccessful();

    Notification::assertSentTo($admin, AnniversaryNotification::class, function ($notification) use ($member) {
        return $notification->member->id === $member->id;
    });
});
