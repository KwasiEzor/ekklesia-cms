<?php

namespace Database\Factories;

use App\Models\Campus;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampusFactory extends Factory
{
    protected $model = Campus::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->city().' Campus',
            'address' => $this->faker->optional(0.8)->address(),
            'city' => $this->faker->optional(0.9)->city(),
            'country' => $this->faker->optional(0.9)->country(),
            'phone' => $this->faker->optional(0.7)->phoneNumber(),
            'email' => $this->faker->optional(0.6)->safeEmail(),
            'pastor_name' => $this->faker->optional(0.8)->name(),
            'capacity' => $this->faker->optional(0.5)->numberBetween(50, 2000),
            'is_main' => false,
            'custom_fields' => null,
        ];
    }

    public function main(): static
    {
        return $this->state(fn (): array => [
            'is_main' => true,
        ]);
    }
}
