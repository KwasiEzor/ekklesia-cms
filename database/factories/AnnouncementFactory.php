<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(4);

        return [
            'tenant_id' => Tenant::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'body' => $this->faker->optional(0.8)->paragraphs(2, true),
            'published_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', '+1 week'),
            'expires_at' => $this->faker->optional(0.5)->dateTimeBetween('+1 week', '+3 months'),
            'pinned' => $this->faker->boolean(20),
            'target_group' => $this->faker->optional(0.4)->randomElement(['youth', 'women', 'men', 'all', 'leaders']),
            'custom_fields' => null,
        ];
    }

    public function pinned(): static
    {
        return $this->state(fn () => [
            'pinned' => true,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'published_at' => $this->faker->dateTimeBetween('-3 months', '-1 month'),
            'expires_at' => $this->faker->dateTimeBetween('-1 week', '-1 day'),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'published_at' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'expires_at' => $this->faker->dateTimeBetween('+1 week', '+3 months'),
        ]);
    }
}
