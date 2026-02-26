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
            'baptism_date' => $this->faker->optional(0.5)->dateTimeBetween('-10 years', '-1 month'),
            'cell_group_id' => null,
            'status' => 'active',
            'custom_fields' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => 'active',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => 'inactive',
        ]);
    }

    public function visiting(): static
    {
        return $this->state(fn () => [
            'status' => 'visiting',
        ]);
    }
}
