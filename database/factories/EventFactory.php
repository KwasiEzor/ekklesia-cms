<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(3);
        $startAt = $this->faker->dateTimeBetween('-1 month', '+3 months');
        $endAt = (clone $startAt)->modify('+' . $this->faker->numberBetween(1, 4) . ' hours');

        return [
            'tenant_id' => Tenant::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'start_at' => $startAt,
            'end_at' => $this->faker->optional(0.8)->passthrough($endAt),
            'location' => $this->faker->optional(0.9)->address(),
            'description' => $this->faker->optional(0.7)->paragraphs(2, true),
            'image' => null,
            'registration_url' => $this->faker->optional(0.4)->url(),
            'capacity' => $this->faker->optional(0.5)->numberBetween(20, 500),
            'custom_fields' => null,
        ];
    }

    public function upcoming(): static
    {
        return $this->state(fn () => [
            'start_at' => $this->faker->dateTimeBetween('+1 day', '+3 months'),
        ]);
    }

    public function past(): static
    {
        return $this->state(fn () => [
            'start_at' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
            'end_at' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
        ]);
    }
}
