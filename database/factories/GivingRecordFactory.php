<?php

namespace Database\Factories;

use App\Models\GivingRecord;
use App\Models\Member;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class GivingRecordFactory extends Factory
{
    protected $model = GivingRecord::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'member_id' => null,
            'amount' => $this->faker->randomFloat(2, 500, 500000),
            'currency' => $this->faker->randomElement(['XOF', 'EUR', 'USD', 'XAF']),
            'date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'method' => $this->faker->randomElement(['mobile_money', 'cash', 'bank_transfer', 'card']),
            'reference' => $this->faker->optional(0.7)->regexify('[A-Z0-9]{12}'),
            'campaign_id' => null,
            'custom_fields' => null,
        ];
    }

    public function withMember(?Member $member = null): static
    {
        return $this->state(fn () => [
            'member_id' => $member?->id ?? Member::factory(),
        ]);
    }

    public function anonymous(): static
    {
        return $this->state(fn () => [
            'member_id' => null,
        ]);
    }

    public function mobileMoney(): static
    {
        return $this->state(fn () => [
            'method' => 'mobile_money',
        ]);
    }

    public function cash(): static
    {
        return $this->state(fn () => [
            'method' => 'cash',
        ]);
    }

    public function inCurrency(string $currency): static
    {
        return $this->state(fn () => [
            'currency' => $currency,
        ]);
    }
}
