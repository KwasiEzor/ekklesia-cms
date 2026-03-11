<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campaign>
 */
class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        $names = [
            'Construction du nouveau temple',
            'Mission Cameroun 2026',
            'Achat d\'instruments de musique',
            'Rénovation de la salle',
            'Camp de jeunes été 2026',
            'Aide aux orphelins',
            'Équipement sonorisation',
            'Programme alimentaire',
            'Bourse scolaire enfants',
            'Conférence annuelle',
        ];

        $startDate = $this->faker->dateTimeBetween('-3 months', '+1 month');

        return [
            'tenant_id' => Tenant::factory(),
            'fund_id' => null,
            'name' => $this->faker->randomElement($names),
            'description' => $this->faker->optional(0.8)->paragraph(),
            'goal_amount' => $this->faker->randomElement([500000, 1000000, 2000000, 5000000, 10000000]),
            'currency' => $this->faker->randomElement(['XOF', 'XAF', 'EUR', 'USD']),
            'start_date' => $startDate,
            'end_date' => $this->faker->optional(0.7)->dateTimeBetween($startDate, '+12 months'),
            'status' => 'active',
            'image' => null,
            'custom_fields' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'completed',
            'start_date' => $this->faker->dateTimeBetween('-12 months', '-3 months'),
            'end_date' => $this->faker->dateTimeBetween('-3 months', '-1 week'),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => 'draft',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => 'cancelled',
        ]);
    }

    public function withoutGoal(): static
    {
        return $this->state(fn (): array => [
            'goal_amount' => null,
        ]);
    }
}
