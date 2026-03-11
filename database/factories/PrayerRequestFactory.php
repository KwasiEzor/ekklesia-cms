<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\PrayerRequest;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrayerRequest>
 */
class PrayerRequestFactory extends Factory
{
    protected $model = PrayerRequest::class;

    public function definition(): array
    {
        $categories = ['guérison', 'provision', 'famille', 'travail', 'études', 'délivrance', 'mariage', 'voyage'];

        return [
            'tenant_id' => Tenant::factory(),
            'member_id' => Member::factory(),
            'type' => $this->faker->randomElement(['prayer', 'praise', 'testimony']),
            'visibility' => $this->faker->randomElement(['public', 'group', 'confidential']),
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement($categories),
            'status' => 'active',
            'is_anonymous' => false,
            'is_featured' => false,
            'custom_fields' => null,
        ];
    }

    public function answered(): static
    {
        return $this->state(fn (): array => [
            'status' => 'answered',
            'answered_at' => now(),
            'testimony' => $this->faker->paragraph(),
        ]);
    }

    public function anonymous(): static
    {
        return $this->state(fn (): array => [
            'is_anonymous' => true,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (): array => [
            'is_featured' => true,
        ]);
    }
}
