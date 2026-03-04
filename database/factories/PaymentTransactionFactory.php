<?php

namespace Database\Factories;

use App\Models\PaymentTransaction;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'amount' => $this->faker->randomFloat(2, 500, 50000),
            'currency' => $this->faker->randomElement(['XOF', 'XAF', 'EUR', 'USD']),
            'provider' => $this->faker->randomElement(['cinetpay', 'stripe']),
            'provider_reference' => $this->faker->optional(0.6)->uuid(),
            'status' => 'pending',
            'payment_method' => $this->faker->randomElement(['mtn_momo', 'orange_money', 'wave', 'card']),
            'phone_number' => $this->faker->optional(0.7)->phoneNumber(),
            'campaign_id' => $this->faker->optional(0.3)->word(),
            'provider_metadata' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'completed',
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => 'Insufficient funds',
        ]);
    }

    public function cinetpay(): static
    {
        return $this->state(fn (): array => [
            'provider' => 'cinetpay',
            'currency' => 'XOF',
            'payment_method' => $this->faker->randomElement(['mtn_momo', 'orange_money', 'wave']),
        ]);
    }

    public function stripe(): static
    {
        return $this->state(fn (): array => [
            'provider' => 'stripe',
            'payment_method' => 'card',
        ]);
    }
}
