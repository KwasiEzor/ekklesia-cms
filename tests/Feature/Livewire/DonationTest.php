<?php

use App\Livewire\Donation;
use App\Models\Fund;
use App\Models\Tenant;
use App\Services\Payment\PaymentService;
use Livewire\Livewire;
use Mockery\MockInterface;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    tenancy()->initialize($this->tenant);

    \Illuminate\Support\Facades\Route::get('/donation/success')->name('donation.success');
});

test('donation component renders correctly', function () {
    Fund::factory()->create([
        'name' => 'General Fund', 
        'is_active' => true,
        'tenant_id' => $this->tenant->id
    ]);

    Livewire::test(Donation::class)
        ->assertSee('Support Our Mission')
        ->assertSee('General Fund')
        ->assertSet('amount', 1000);
});
test('donation component validates input', function () {
    Livewire::test(Donation::class)
        ->set('amount', 50) // less than min 100
        ->set('customerEmail', 'invalid-email')
        ->call('donate')
        ->assertHasErrors(['amount', 'customerEmail', 'customerName', 'phoneNumber']);
});

test('donation component initiates payment and redirects', function () {
    $this->mock(PaymentService::class, function (MockInterface $mock) {
        $mock->shouldReceive('initiatePayment')
            ->once()
            ->andReturn([
                'status' => 'pending',
                'payment_url' => 'https://pay.example.com',
                'failure_reason' => null,
            ]);
    });

    Livewire::test(Donation::class)
        ->set('amount', 5000)
        ->set('customerName', 'John Doe')
        ->set('customerEmail', 'john@example.com')
        ->set('phoneNumber', '+22890000000')
        ->call('donate')
        ->assertRedirect('https://pay.example.com');
});

test('donation component handles payment failure', function () {
    $this->mock(PaymentService::class, function (MockInterface $mock) {
        $mock->shouldReceive('initiatePayment')
            ->once()
            ->andReturn([
                'status' => 'failed',
                'payment_url' => null,
                'failure_reason' => 'Payment provider rejected',
            ]);
    });

    Livewire::test(Donation::class)
        ->set('amount', 5000)
        ->set('customerName', 'John Doe')
        ->set('customerEmail', 'john@example.com')
        ->set('phoneNumber', '+22890000000')
        ->call('donate')
        ->assertSet('errorMessage', 'Payment provider rejected')
        ->assertNoRedirect();
});
