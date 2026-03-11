<?php

namespace Database\Factories;

use App\Models\Household;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Household>
 */
class HouseholdFactory extends Factory
{
    protected $model = Household::class;

    public function definition(): array
    {
        $lastName = $this->faker->lastName();

        return [
            'tenant_id' => Tenant::factory(),
            'name' => "Famille {$lastName}",
            'address' => $this->faker->optional(0.7)->streetAddress(),
            'city' => $this->faker->optional(0.7)->city(),
            'phone' => $this->faker->optional(0.5)->phoneNumber(),
            'custom_fields' => null,
        ];
    }
}
