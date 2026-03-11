<?php

namespace Database\Factories;

use App\Models\Fund;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fund>
 */
class FundFactory extends Factory
{
    protected $model = Fund::class;

    public function definition(): array
    {
        $types = ['tithe', 'offering', 'building', 'missions', 'benevolence', 'other'];
        $names = [
            'tithe' => ['Dîmes', 'Dîmes mensuelles'],
            'offering' => ['Offrandes', 'Offrandes du dimanche', 'Offrandes spéciales'],
            'building' => ['Construction du temple', 'Rénovation', 'Fonds bâtiment'],
            'missions' => ['Missions évangéliques', 'Fonds missionnaire', 'Évangélisation'],
            'benevolence' => ['Entraide', 'Fonds de secours', 'Aide aux nécessiteux'],
            'other' => ['Fonds général', 'Projets spéciaux', 'Fonds divers'],
        ];

        $type = $this->faker->randomElement($types);

        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->randomElement($names[$type]),
            'description' => $this->faker->optional(0.7)->sentence(),
            'type' => $type,
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 10),
            'custom_fields' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }

    public function ofType(string $type): static
    {
        return $this->state(fn (): array => [
            'type' => $type,
        ]);
    }
}
