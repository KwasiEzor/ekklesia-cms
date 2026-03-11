<?php

namespace Database\Factories;

use App\Models\ReadingPlan;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingPlanFactory extends Factory
{
    protected $model = ReadingPlan::class;

    public function definition(): array
    {
        $plans = [
            ['title' => 'Lire la Bible en 365 jours', 'days' => 365],
            ['title' => 'Les Psaumes en 30 jours', 'days' => 30],
            ['title' => 'L\'Évangile de Jean en 21 jours', 'days' => 21],
            ['title' => 'Les Proverbes en 31 jours', 'days' => 31],
            ['title' => 'Les Épîtres de Paul en 60 jours', 'days' => 60],
            ['title' => 'Le Nouveau Testament en 90 jours', 'days' => 90],
            ['title' => 'La Genèse en 14 jours', 'days' => 14],
            ['title' => 'Les Actes des Apôtres en 28 jours', 'days' => 28],
        ];

        $plan = $this->faker->randomElement($plans);

        return [
            'tenant_id' => Tenant::factory(),
            'title' => $plan['title'],
            'description' => $this->faker->sentence(),
            'duration_days' => $plan['days'],
            'grace_period_days' => 1,
            'is_active' => true,
            'custom_fields' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }

    public function withGracePeriod(int $days): static
    {
        return $this->state(fn (): array => [
            'grace_period_days' => $days,
        ]);
    }
}
