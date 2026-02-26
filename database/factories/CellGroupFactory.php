<?php

namespace Database\Factories;

use App\Models\CellGroup;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class CellGroupFactory extends Factory
{
    protected $model = CellGroup::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->randomElement([
                'Groupe Béthel',
                'Groupe Shalom',
                'Groupe Emmanuel',
                'Groupe Grâce',
                'Groupe Maranatha',
                'Groupe Siloé',
                'Groupe Horeb',
                'Groupe Sion',
            ]),
            'description' => $this->faker->optional(0.6)->sentence(),
            'custom_fields' => null,
        ];
    }
}
