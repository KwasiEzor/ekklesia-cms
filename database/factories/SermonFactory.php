<?php

namespace Database\Factories;

use App\Models\Sermon;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SermonFactory extends Factory
{
    protected $model = Sermon::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(4);

        return [
            'tenant_id' => Tenant::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'speaker' => $this->faker->name(),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'duration' => $this->faker->numberBetween(1200, 5400), // 20-90 min
            'audio_url' => $this->faker->optional(0.7)->url(),
            'video_url' => $this->faker->optional(0.5)->url(),
            'transcript' => $this->faker->optional(0.3)->paragraphs(3, true),
            'tags' => $this->faker->randomElements(
                ['foi', 'prière', 'guérison', 'famille', 'jeunesse', 'louange', 'évangélisation'],
                $this->faker->numberBetween(1, 3)
            ),
            'custom_fields' => null,
        ];
    }
}
