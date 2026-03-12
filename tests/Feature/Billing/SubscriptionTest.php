<?php

use App\Filament\Pages\Billing;
use App\Models\PlanLimit;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->tenant = Tenant::create([
        'id' => 'test-tenant',
        'name' => 'Test Tenant',
        'slug' => 'test-tenant',
        'plan_slug' => 'free',
    ]);

    tenancy()->initialize($this->tenant);
    app()->setLocale('en');

    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);

    actingAs($this->user);
    Filament::setTenant($this->tenant);

    PlanLimit::create([
        'plan_slug' => 'free',
        'name' => 'Free',
        'price_cents' => 0,
        'max_members' => 10,
    ]);

    PlanLimit::create([
        'plan_slug' => 'premium',
        'name' => 'Premium',
        'price_cents' => 450000,
        'stripe_price_id' => 'price_123',
        'max_members' => 1000,
    ]);
});

test('billing page can be rendered', function () {
    Livewire::test(Billing::class)
        ->assertSuccessful()
        ->assertSee('Free');
});

test('billing page shows usage summary', function () {
    Livewire::test(Billing::class)
        ->assertSee('Members')
        ->assertSee('Campuses')
        ->assertSee('Storage');
});

test('billing page shows plans', function () {
    Livewire::test(Billing::class)
        ->assertSee('Free')
        ->assertSee('Premium');
});

test('cancel action is visible only when subscribed', function () {
    // Not subscribed initially
    Livewire::test(Billing::class)
        ->assertDontSee('Cancel Subscription');
});
