<?php

namespace Database\Factories;

use App\Models\DevotionalSeries;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DevotionalSeries>
 */
class DevotionalSeriesFactory extends Factory
{
    protected $model = DevotionalSeries::class;

    public function definition(): array
    {
        $titles = [
            'Les Psaumes de David',
            'Marcher avec Dieu',
            'La foi en action',
            'Les béatitudes',
            'Les paraboles de Jésus',
            'Vivre par l\'Esprit',
            'Les fruits de l\'Esprit',
            'La prière efficace',
            'Les promesses de Dieu',
            'Le Sermon sur la montagne',
        ];

        return [
            'tenant_id' => Tenant::factory(),
            'title' => $this->faker->randomElement($titles),
            'description' => $this->faker->optional(0.7)->paragraph(),
            'image' => null,
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
