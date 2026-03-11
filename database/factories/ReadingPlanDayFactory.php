<?php

namespace Database\Factories;

use App\Models\ReadingPlan;
use App\Models\ReadingPlanDay;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingPlanDayFactory extends Factory
{
    protected $model = ReadingPlanDay::class;

    public function definition(): array
    {
        $passages = [
            'Genèse 1-3', 'Psaume 1', 'Psaume 23', 'Proverbes 1:1-19',
            'Jean 1:1-18', 'Jean 3:1-21', 'Romains 8:1-17', 'Philippiens 4:4-13',
            'Matthieu 5:1-16', 'Ésaïe 40:28-31', '1 Corinthiens 13',
        ];

        return [
            'tenant_id' => Tenant::factory(),
            'reading_plan_id' => ReadingPlan::factory(),
            'day_number' => $this->faker->numberBetween(1, 365),
            'title' => $this->faker->optional(0.7)->sentence(4),
            'passage_reference' => $this->faker->randomElement($passages),
            'passage_text' => $this->faker->optional(0.5)->paragraph(),
            'reflection' => $this->faker->optional(0.5)->paragraph(),
        ];
    }
}
