<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->boolean(80) ? $this->faker->unique()->safeEmail() : null,
            'phone' => $this->faker->optional(0.7)->phoneNumber(),
            'date_of_birth' => $this->faker->optional(0.6)->dateTimeBetween('-60 years', '-10 years'),
            'wedding_anniversary' => $this->faker->optional(0.3)->dateTimeBetween('-30 years', '-1 year'),
            'baptism_date' => $this->faker->optional(0.5)->dateTimeBetween('-10 years', '-1 month'),
            'cell_group_id' => null,
            'status' => 'active',
            'custom_fields' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => 'active',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => 'inactive',
        ]);
    }

    public function visiting(): static
    {
        return $this->state(fn (): array => [
            'status' => 'visiting',
        ]);
    }

    public function birthdayToday(): static
    {
        return $this->state(fn (): array => [
            'date_of_birth' => now()->subYears(random_int(15, 60)),
        ]);
    }

    public function anniversaryToday(): static
    {
        return $this->state(fn (): array => [
            'wedding_anniversary' => now()->subYears(random_int(1, 30)),
        ]);
    }
}
